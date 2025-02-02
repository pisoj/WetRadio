const recordings = document.querySelectorAll(".recording");

for(let i = 0; i < recordings.length; i++) {
  const recording = recordings[i];
  const file_labels_raw = recording.querySelectorAll("noscript");
  const file_audios = Array.from(recording.querySelectorAll("audio"));
  let file_labels = [];
  for(let j = 0; j < file_labels_raw.length; j++) {
    let inside = new DOMParser().parseFromString(file_labels_raw[j].innerText, "text/html");
    let h3 = inside.querySelector("h3");
    if(!h3) {
      let audio = inside.querySelector("audio");
      if(!audio) continue;
      file_audios.push(audio)
      continue;
    };
    file_labels.push(h3.innerText)
  }
  const selectField = recording.querySelector("select");
  for(let j = 0; j < file_labels.length; j++) {
    const option = document.createElement("option");
    option.setAttribute("value", file_audios[j].innerText);
    option.innerText = file_labels[j];
    console.log(file_labels[j].innerText);
    selectField.appendChild(option);
  }

  selectField.addEventListener('input', function (evt) {
    change_active_audio(recording, this.selectedIndex);
    console.log(this.options[this.selectedIndex].text);
  });
}

function change_active_audio(recording, targetIndex) {
  let activeAudio = recording.querySelector("audio");
  activeAudio.pause();
  const currentTime = activeAudio.currentTime;

  const noscript = recording.querySelectorAll("noscript");
  for(let j = 0; j < noscript.length; j++) {
    let inside = new DOMParser().parseFromString(noscript[j].innerText, "text/html");
    const audio = inside.querySelector("audio");
    if(!audio) continue;
    if(audio.getAttribute("data-index") != targetIndex) continue;
    activeAudioOuterHtml = activeAudio.outerHTML;
    activeAudio.outerHTML = audio.outerHTML;
    noscript[j].innerHTML = activeAudioOuterHtml;
    activeAudio = recording.querySelector("audio");
    activeAudio.currentTime = currentTime;
    activeAudio.play();
    break;
  }
}