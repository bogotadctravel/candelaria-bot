<?php
include "api_keys.php";
// process_chat.php
header('Content-Type: application/json');
// Leer la solicitud JSON desde el cuerpo de la solicitud
$request = json_decode(file_get_contents('php://input'), true);
//echo "ok";
//print_r($request);
//exit;
if (isset($request['message'])) {
    //echo $request['threadId'];
    $userMessage = "La fecha actual es: ".date("Y-m-d").". ".$request['message'];
    
    // Crear un nuevo thread
    if($request['threadId']=="")
    {
        $thread = createThread();
        $threadID  = $thread->id;
        error_log("Thread: " . json_encode($thread));  // Log para depuración
    }else {
        $threadID = $request['threadId'];
        
    }
   

    // Ejecutar el asistente *****
    if($request['runId']=="")
    {
        $messageResponse = addMessageToThread($threadID, $userMessage);
        $run = runAssistant($threadID);
        
        //$responsemessage=0;
        
    }else
    {
        $run = runAssistant($threadID,$request['runId']);
   

    }
         $responsemessage=$run[1];
        $runId = $run[0]->id;
        $runStatus = $run[0]->status;
        $resource = $run[2];
        $function = $run[3];
  // echo $run;

    // Obtener la respuesta del asistente
    //$assistantMessages = getAssistantMessages($threadID);
   

   // $responseMessage = $assistantMessages->data[0]->content[0]->text->value;

    // Devolver la respuesta en formato JSON
    echo json_encode(['response' => $responsemessage,"threadId" => $threadID,"runID"=>$runId,"runStatus"=>$runStatus, "resource"=> $resource,"function"=> $function]);exit;
}
function createThread() {
    global $apiKey;

    $ch = curl_init('https://api.openai.com/v1/threads');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json',
        'OpenAI-Beta: assistants=v1'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([]));

    $response = curl_exec($ch);

    curl_close($ch);

    return json_decode($response);
}
function addMessageToThread($threadId, $content) {
    global $apiKey;

    $ch = curl_init("https://api.openai.com/v1/threads/{$threadId}/messages");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json',
        'OpenAI-Beta: assistants=v1'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'role' => 'user',
        'content' => $content
    ]));

    $response = curl_exec($ch);

    curl_close($ch);

    return json_decode($response);
}
function runAssistant($threadId,$runId="") {
    global $assistantId, $apiKey;

    if($runId==""){
       // echo "Inicio el RUN ".$runId;
    $ch = curl_init("https://api.openai.com/v1/threads/{$threadId}/runs");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json',
        'OpenAI-Beta: assistants=v1'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'assistant_id' => $assistantId
    ]));

    $response = curl_exec($ch);
    curl_close($ch);

    $run = json_decode($response);
    $runId = $run->id;
    }else{
        $run = "";
    }
    // Esperar hasta que el run esté completo o requiera acción
    $maxAttempts = 30;
    $waitTime = 1;
   // echo "run previo a revisar ".$runId;
    for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
        $ch = curl_init("https://api.openai.com/v1/threads/{$threadId}/runs/{$runId}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiKey,
            'OpenAI-Beta: assistants=v1'
        ]);

        $runStatusResponse = curl_exec($ch);
        curl_close($ch);

        $runStatus = json_decode($runStatusResponse);
        //echo $runStatus->status." / ";
        if ($runStatus->status === 'requires_action') {
            foreach ($runStatus->required_action->submit_tool_outputs->tool_calls as $toolCall) {
                if (($toolCall->type === 'function' && $toolCall->function->name === 'search_in_visitbogota') || ($toolCall->type === 'function' && $toolCall->function->name === 'search_in_events')) {
                    $toolCallId = $toolCall->id;
                    $functionArguments = json_decode($toolCall->function->arguments, true);

                    // Ejecuta la función y obtén el resultado
                    $result = searchInBogota($functionArguments,$toolCall->function->name);
                   // echo json_encode($result);
                    // Envía la respuesta de la función de vuelta al `run`
                    //print_r($result); exit;
                    $thesubmit = submitFunctionOutput($threadId, $run->id, $toolCallId, $result);
                    return array($runStatus,0, $functionArguments['resource'],$toolCall->function->name);
                }
            }
        }

        if ($runStatus->status === 'completed') {
            $assistantMessages = getAssistantMessages($threadId);
            return array($runStatus,$assistantMessages->data[0]->content[0]->text->value,0,0);
            // return $runStatus;
        }

        sleep($waitTime);
    }

   // return $runStatus;
}
function getAssistantMessages($threadId) {
    global $apiKey;

    // Inicializar un bucle con intentos para verificar la respuesta
  /*  $maxAttempts = 10;  // Máximo número de intentos
    $waitTime = 2;  // Tiempo de espera entre intentos en segundos

    for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {*/
        $ch = curl_init("https://api.openai.com/v1/threads/{$threadId}/messages");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiKey,
            'OpenAI-Beta: assistants=v1'
        ]);

        $response = curl_exec($ch);
        //echo $response;
       // echo "----";
        //print_r($response);
        curl_close($ch);

        $messages = json_decode($response);
        //echo "Respuesta ".$messages->data[0]->content[0]->text->value;
        return $messages;
        // Verificar si el último mensaje en el hilo es del asistente
      /*  if (!empty($messages->data)) {
            $lastMessage = end($messages->data);
            if ($lastMessage->role === 'assistant') {
                return $messages;  // Retorna los mensajes si hay una respuesta del asistente
            }
        }*/

      /*  // Espera antes de reintentar
        sleep($waitTime);
    }*/

    // Si no se obtiene respuesta después de los intentos
    return $messages;
}
function submitFunctionOutput($threadId, $runId, $toolCallId, $output) {
    global $apiKey;
   // echo $threadId." - Run:".$runId." - ToolCall:".$toolCallId." - Output:".$output; exit;
    $ch = curl_init("https://api.openai.com/v1/threads/{$threadId}/runs/{$runId}/submit_tool_outputs");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json',
        'OpenAI-Beta: assistants=v1'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'tool_outputs' => [
            [
                'tool_call_id' => $toolCallId,
                'output' => $output
            ]
        ]
    ]));

    $response = curl_exec($ch);
    curl_close($ch);
   // echo $response;
    //exit;
    return json_decode($response);
}
function searchInBogota($arguments,$functionName) {
    global $apiKey;

    // Verifica que el parámetro 'resource' esté presente en los argumentos
    if (!isset($arguments['resource'])) {
        return [
            'error' => 'Resource parameter is missing.'
        ];
    }

    $resource = $arguments['resource'];
    
    $url = "https://bogotadc.travel/drpl/es/api/v2/candelaria_search/{$resource}";
    if($functionName=="search_in_events")
    {
        $url = "https://bogotadc.travel/drpl/es/api/v2/candelaria_events/{$resource}";
    }
    // Inicializar cURL
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Ejecutar la solicitud y obtener la respuesta
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response === false) {
        return [
            'error' => 'Failed to fetch data from Bogota API.'
        ];
    }

    // Reemplazar los corchetes [] por llaves {} en la respuesta
    $responseModified = str_replace(['[', ']'], ['{', '}'], $response);
    //$responseModified = $response;

    // Decodificar la respuesta JSON modificada para asegurar que es válida
   // $decodedResponse = json_decode($responseModified, true);

    // Verifica si la decodificación fue exitosa
    if (json_last_error() !== JSON_ERROR_NONE) {
        return [
            'error' => 'Invalid JSON response received from Bogota API.'
        ];
    }

    // Retorna la respuesta modificada
    return $responseModified;
}
?>