<?php

require './config.php';

$id = $_GET['id'] ?? 1;
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>::studify::</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <style>

strong {
  cursor: pointer;
}

ol li {
  display: block;
  padding: .35em;
  border-radius: 2em;
  padding-left: 1.25em;
}

h1 {
  color: black;
  text-shadow: 1px 1px 2px black;
  text-transform: lowercase;
}

div.brend {
  position: relative;
  border-radius: 2em;
  background-color: rgba(0,0,0,.1);
}

div.brend:before {
  position: absolute;
  top: 0;
  bottom: 0;
  left: 0;
  right: 0;
  margin: 3em;
  margin-right: 0;
  border-radius: .5em;
  content: '';
  background-image: url('./imgs/isaacn.jpg');
  background-position-x: 60%;
  background-position-y: 25%;
  opacity:.1;
}

div.card {
  transform: translateX(-2.5em);
}

    </style>
  </head>
  <body>
<div class="container d-flex align-items-center justify-content-center vh-100">
  <div class="brend w-25 h-75 d-flex align-items-center justify-content-center shadow">
  <h1>studify</h1>
  </div>
  <div class="card  w-75 overflow-hidden h-75 shadow rounded-5">
      <div class="card-header bg-white border-white d-flex p-3 px-4">
          <audio controls></audio>
          <select class="form-select ms-auto w-25 border-white rounded-pill bg-success bg-opacity-10">
            <option></option>
<?php
  echo get_options();
?>
          </select>
      </div>
    <div class="card-body overflow-auto p-5 border-white border-top border-bottom">
<?php
  playlist(get_yaml($id));
?>
    </div>
    <div class="card-footer bg-white border-white d-flex p-3 px-4 justify-content-end">
  <button disabled data-type="start-record" class="btn btn-success bg-opacity-10 rounded-pill me-2">record</button>
  <button disabled data-type="pause-record" class="btn btn-success bg-opacity-10 rounded-pill me-2">pause</button>
  <button disabled data-type="resume-record" class="btn btn-success bg-opacity-10 rounded-pill me-2">resume</button>
  <button disabled data-type="stop-record" class="btn btn-success bg-opacity-10 rounded-pill me-2">finish</button>

  <button disabled data-type="save-record" class="btn btn-success bg-opacity-10 rounded-pill me-2">save</button>
    </div>
  </div>
</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
    <script>
(function () {
  let stream = null;
  let record = null; 
  let file = null;
  let blob = [];
  let text = null;

  const audio = document.querySelector('audio');

  const Buttons = {

    start: null,
    stop: null,
    save: null,
    pause: null,
    resume: null,

    disabling : () => {
      const self = Buttons;
      Object
        .keys(Buttons)
        .filter(k => self[k] instanceof HTMLButtonElement && !self[k].disabled)
        .forEach(k => self[k].disabled = true);
    },

    enabling : (...keys) => {
      console.log(keys);
      const self = Buttons;
      keys
        .filter(k => self[k] instanceof HTMLButtonElement && self[k].disabled)
        .forEach(k => self[k].disabled = false);
    },

    initing: () => {
      const self = Buttons;
      Object
        .keys(self)
        .filter(k => self[k] === null)
        .forEach(k => self[k] = document.querySelector(`[data-type="${k}-record"]`));
    },

    recording: () => {
      Buttons.disabling();
      Buttons.enabling('pause','stop');
    },

    pausing: () => {
      Buttons.disabling();
      Buttons.enabling('resume','stop');
    },

    finishing: () => {
      Buttons.disabling();
      Buttons.enabling('save');
    },

    saving: () => {
      Buttons.disabling();
      Buttons.enabling('start');
    }

  };

  Buttons.initing();


  function play (src) {
    audio.src = `<?=SOUND_PATH?>/${src}`;
    audio.load();
    Audio.playbackRate = 1;
    audio.play();
  }

  function playlist (value) {
    document.location.href = '?id='+ value;
  }

  async function getRecorder () {
    try {
      stream = await navigator.mediaDevices.getUserMedia({audio: true});
      return new MediaRecorder(stream);
    } catch (err) {
      throw err;
    }
  }

  async function recording () {

    try {
      record = await getRecorder ();

      record.addEventListener("dataavailable", e => {
        blob.push(e.data);
      });

      record.addEventListener("stop", e => {
        if (e.data)
          blob.push(e.data);

        file = new File(blob, Date.now(), {type: record.mimeType});
        blob = [];
        record = null;
        stream.getTracks()[0].stop();
      });

      record.start();
      Buttons.recording();

    } catch (ex) {
      console.log('ERRO', ex);
    }

  }

  function pause () {
    Buttons.pausing();
    if (!record)
      return;
    record.pause();
  }

  function resume () {
    Buttons.recording();
    if (!record)
      return;
    record.resume();
  }

  function finish () {
    Buttons.finishing();
    if (!record)
      return;
    record.stop();
  }

  function save () {

    Buttons.disabling();
    if (!file) return;

    const body = new FormData();
    body.append('file', file);
    body.append('id', <?=$id?>);
    body.append('text', text.textContent);

    fetch ('/save.php', {method: 'POST', body: body})
      .then(r => r.text())
      .then(txt => {
        const link = document.createElement('a');
        link.textContent = text.textContent;
        link.href = 'javascript:void(0)';
        link.classList.add('text-success');
        link.dataset.data = JSON.stringify([file.name + '.webm']);

        text.parentNode.style.removeProperty('background');
        text.parentNode.replaceChild(link, text);

        text = null;
        file = null;
      })
      .catch(ex => console.error(ex));

  }

  function handleLink (element) {
    const data = JSON.parse(element.dataset.data);
    play(data[0]);
  }

  function handleSelect (element) {
    const value = element.options[element.selectedIndex].value;
    playlist(value);
  }

  function handleButton (element) {
    const type = element.dataset.type;
    switch(type) {
      case "start-record":
        return recording();
      case "stop-record":
        return finish();
      case "save-record":
        return save();
      case "pause-record":
        return pause();
      case "resume-record":
        return resume();
    }

  }

  function handleSectionList (element) {
    const articles = element.closest('.accordion-item').querySelectorAll('a');

    let audios = [];
    articles.forEach(e => {
      audios = audios.concat(JSON.parse(e.dataset.data));
      console.log(JSON.parse(e.dataset.data));
    });

    if (!audios.length)
      return;

    play(audios.shift());
    audio.onended = e => {
      if (audios.length)
        play(audios.shift());
    }
 
  }

  function handleStrong (element) {
    if (record)
      return;

    if (text)
      text.parentNode.style.removeProperty('background');
    text = element;
    element.parentNode.style.background = '#e0e0e0';
    Buttons.enabling('start');
  }

  function clickCtr (e) {
    const element = e.target;
    switch (element.nodeName) {
      case 'A':
        return handleLink(element);
      case 'BUTTON':
        return handleButton(element);
      case 'STRONG':
        return handleStrong(element);
      case 'SPAN':
        return handleSectionList(element);
    }
  }

  function changeCtr (e) {
    const element = e.target;
    if (element.nodeName === 'SELECT')
      return handleSelect(element);
  }

  document.body.addEventListener('click', clickCtr);
  document.body.addEventListener('change', changeCtr);

}());
    </script>
  </body>
</html>
