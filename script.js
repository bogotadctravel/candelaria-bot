var threadId = "";
var runId = "";
var currentMan = {
  name: "Candelaria",
  source: "cha_CBoB2TwZi50OnRUpbWQMx",
  avatar: "sticker/asisstant2.webp",
};

function botonCheck(input) {
  if (input.checked) useChatBot();
}

window.onload = function () {
  // Agregar un mensaje de bienvenida por parte del asistente
  addMessageToChat("assistant", "Escribiendo...", true);
  addMessageToChat(
    "assistant",
    "¡Hola! Soy Candelaria, la asistente virtual de turismo de Bogotá. ¿En qué puedo ayudarte hoy?"
  );
};
document.getElementById("send-btn").addEventListener("click", async () => {
  const userInput = document.getElementById("themsg").value;

  if (!userInput) return;

  addMessageToChat("user", userInput, false);
  addMessageToChat("assistant", "Escribiendo...", true);
  document.getElementById("themsg").value = ""; // Limpiar campo de entrada
  var response = await sendMessageToServer(userInput);
  if (response != 0) {
    addMessageToChat("assistant", response, false);
  } else {
    var response2 = await sendMessageToServer(userInput);
    addMessageToChat("assistant", response2, false);

    console.log("Ok", runId);
  }
});

function addMessageToChat(role, message, temp = false) {
  const messagesDiv = document.getElementById("history");
  const messageDiv = document.createElement("li");

  messageDiv.classList.add('message-data', role);
  messageDiv.classList.add('message', role);
  if(temp && role == "assistant"){ messageDiv.classList.add('temporal'); }
  if(role=="assistant" && !temp){
      document.querySelector('.temporal').remove(); 
  }

  messageDiv.innerHTML = parseMarkdown(message);

  messagesDiv.appendChild(messageDiv);
  document.getElementById('chat-history').scrollTop = document.getElementById('chat-history').scrollHeight;  // Desplazar hacia abajo
}

async function sendMessageToServer(message) {
  console.log({ message: message, threadId: threadId, runId: runId });
  try {
    //console.log(message);
    const response = await fetch("process_chat.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        message: message,
        threadId: threadId,
        runId: runId,
      }),
    });
    const data = await response.json();
    // console.log();
    threadId = data.threadId;
    runId = data.runID;
    if (data.runStatus == "completed") {
      runId = "";
      console.log("Completed");
    }

    return data.response;
  } catch (error) {
    console.error("Error al comunicarse con el servidor:", error);
    return "Hubo un error al comunicarse con el servidor.";
  }
}
function parseMarkdown(text) {
  // Reemplazar encabezados (## Header)
  text = text.replace(/## (.+)/g, "<h2>$1</h2>"); // Encabezado de nivel 2
  text = text.replace(/# (.+)/g, "<h1>$1</h1>"); // Encabezado de nivel 1

  // Negritas (**text**)
  text = text.replace(/\*\*(.+?)\*\*/g, "<strong>$1</strong>");

  // Itálicas (*text*)
  text = text.replace(/\*(.+?)\*/g, "<em>$1</em>");

  // Enlaces ([text](url))
  text = text.replace(
    /\[(.+?)\]\((https?:\/\/[^\s]+)\)/g,
    '<a href="$2">$1</a>'
  );

  // Saltos de línea
  text = text.replace(/\n/g, "<br>");

  return text;
}
function encender() {
  var botonRebeca = document.getElementById("btn-rebeca");

  if (botonRebeca.className == "btn-chat") {
    botonRebeca.className = "btn-equis";
  } else {
    botonRebeca.className = "btn-chat";
  }
}
