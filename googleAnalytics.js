// Función general para enviar eventos a Google Analytics
function sendAnalyticsEvent(
    category,
    action,
    label,
    additionalData = {},
    eventName
  ) {
    // Utiliza el nombre del evento proporcionado o un valor predeterminado ('custom_event')
    console.log("Evento enviado a Google Analytics");
    const eventToTrack = eventName || "custom_event";
    console.log("Data adicional: ", additionalData);
    // Envía el evento a Google Analytics
    // gtag("event", eventToTrack, {
    //   event_category: category,
    //   event_action: action,
    //   event_label: label,
    //   ...additionalData,
    // });
  }

  function useChatBot() {
    // Llama a sendAnalyticsEvent con el ID del usuario
    console.log("Tracking Login Success");
    sendAnalyticsEvent(
      "success",
      "chatbot",
      "Persona usó el boton del chatbot",
      {},
      "chatbot"
    );
  }

  function sendAnswer() {
    // Llama a sendAnalyticsEvent con el ID del usuario
    console.log("Answer Success");
    sendAnalyticsEvent(
      "success",
      "chatbot",
      "Se ha registrado una respuesta",
      {},
      "chatbot"
    );
  }
  

  
  
