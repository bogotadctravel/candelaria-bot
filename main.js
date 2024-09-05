var candidates = [
  {
    name: "Candelaria",
    source: "cha_CBoB2TwZi50OnRUpbWQMx",
    avatar: "sticker/asisstant2.webp"
  }
];

document.addEventListener("DOMContentLoaded", () => {
  newChat(0);
});

var currentMan = {};
var rows = [];

function newChat(id) {
  $("#themsg").focus();
  $("#theform").show();
  $(".man").removeClass("active");
  currentMan = candidates[id];
  $("#history").html("");
  setMSG(
    `¡Hola! Soy ${currentMan.name}, tu asistente virtual. Aquí podrás explorar los atractivos turísticos más emblemáticos de Bogotá. ¿En que te puedo ayudar hoy?`,
    1
  );
  $("#current-man").html(`Preguntale a  ${currentMan.name}`);
  $("#current-avatar").attr("src", currentMan.avatar);
  $("#cand-" + id).addClass("active");
}
function setMSG(c, p) {
  $(".susp").hide();
  if (p == 2) {
    var element = `<li class="clearfix"><div class="message-data susp text-right"><img src="${currentMan.avatar}" alt="avatar"> </div> <div class="susp message other-message float-right">${c}</div></li>`;
  } else if (p == 1) {
    var element = `<li class="clearfix"><div class="message-data text-right"><img src="${currentMan.avatar}" alt="avatar"> </div> <div class="message other-message float-right" style="text-align:left;">${c}</div></li>`;
  } else {
    var element = `<li class="clearfix"><div class="message-data"></div><div class="message my-message">${c}</div></li>`;
  }

  $("#history").append(element);
  $("#chat-history").animate({ scrollTop: $("#history").height() }, 300);
}
function shuffle(array) {
  let currentIndex = array.length,
    randomIndex;

  // While there remain elements to shuffle.
  while (currentIndex > 0) {
    // Pick a remaining element.
    randomIndex = Math.floor(Math.random() * currentIndex);
    currentIndex--;

    // And swap it with the current element.
    [array[currentIndex], array[randomIndex]] = [
      array[randomIndex],
      array[currentIndex],
    ];
  }

  return array;
}

function botonCheck(input) {
  if(input.checked) 
  useChatBot();
} 

$(document).ready(function () {
  candidates = shuffle(candidates);
  // setCredits("#bc1ef9", "eligeaicredits");

  for (var i = 0; i < candidates.length; i++) {
    var element = `<li class="clearfix man" data-candidate="${i}" id="cand-${i}" ><img src="${candidates[i].avatar}" alt="avatar"><div class="about"><div class="name">${candidates[i].name} </div><div class="status"> <i class="fa fa-circle online"></i> online </div></div> </li>`;
    $("#chatlist").append(element);
  }
  $(".man").click(function () {
    newChat($(this).data("candidate"));
  });

  
  //newChat(0);

  // const btn = document.getElementById('btn-chat')
  //   btn.addEventListener('click', () => useChatBot());

  $("form").on("submit", function (e) {
    e.preventDefault(); // prevent native submit
    var msg = $("#themsg").val();
    var msgObject = [];

    const textoOff = document.getElementById('themsg');
    textoOff.disabled = true; 

    msgObject.push({
      role: "user",
      content: `Actúa como Candelaria el asistente virtual para VisitBogota y responde la siguiente pregunta: ${msg} Explica tu respuesta en lenguaje sencillo y amigable para personas sin conocimiento técnico sobre RenoBo.
      Evita escribir el saludo con cada respuesta que das.
      Formatea siempre las respuestas en formato HTML pero dentro de la respuesta omite que diga que está dentro de un archivo html. Basa tu respuesta específicamente en lo que encuentras en el archivo PDF que te suministramos; en caso de no encontrar la respuesta en el archivo PDF, ofrece el siguiente enlace 'https://visitbogota.co/es/experiencias-turisticas'. Este enlace servirá para ampliar la búsqueda en el sitio web de Visit Bogota.
      Si la pregunta está relacionada con algún proyecto, usa toda la información que esté dentro del archivo PDF. Por ejemplo, si pregunto sobre los teators, relaciona la pregunta con los titulos y ofrece la información relacionada. Responde con la estructura que se encuentra en el archivo PDF, suministrando siempre el enlace.
      Evita responder que obtuviste la información del documento PDF, es irrelevante para el usuario. Por ejemplo, si en la respuesta se va ofrecer algún link, omitir que se extraen del documento PDF y simplemente mostrarle la respuesta.
      Si la pregunta no es clara o no tenemos respuesta, ofrece los enlaces que lo relacionen y que existan dentro del documento, mostrando el paso a paso.
      Que todos los enlaces se abran en una nueva ventana.
      `,
    });

    var entity = {
      url: "https://api.chatpdf.com/v1/chats/message",
      method: "POST",
      timeout: 0,
      headers: {
        "x-api-key": "sec_GXBwYd6ufRuaiBmyg1MTzjMw7YoNkMXQ",
        "Content-Type": "application/json",
      },
      data: JSON.stringify({
        sourceId: currentMan.source,
        messages: msgObject,
      }),
    };
    $("form")[0].reset();
    setMSG(msg, 0);
    setMSG("escribiendo...", 2);
    //console.log(entity);
    $.ajax(entity).done(function (response) {
      // console.log(response);
      msgObject.push({
        role: "assistant",
        content: response.content,
      });
      setMSG(response.content, 1);
      textoOff.disabled = false;
    });
  });
});
