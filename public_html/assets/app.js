// Live players
const curve = document.querySelector(".curve");
const players = document.querySelectorAll("#live > .player");
const audioPlayers = [];
for (let i = 0; i < players.length; i++) {
  const player = players[i];
  const endpoints = player.querySelector(".endpoints");
  audioPlayers.push(
    new IcecastMetadataPlayer(JSON.parse(endpoints.innerText), {
      endpointOrder: endpoints.getAttribute("data-order"),
      onMetadata: (metadata) => {
        const titleElement = player.querySelector(".player-info > h4 > b");
        const subtitleElement = player.querySelector(".player-info > p");

        let title, artist, album;
        // Prefer ogg metadata
        if (metadata.TITLE) {
          title = metadata.TITLE;
          artist = metadata.ARTIST;
          album = metadata.ALBUM;
        } else {
          const info = metadata.StreamTitle.replace(/\s*-\s*/g, "-").split("-");
          title = info[1] ? info[1] : info[0];
          artist = info[1] ? info[0] : "";
          album = info.slice(2).join(" - ");
        }
        titleElement.innerHTML = title + "&nbsp;";
        subtitleElement.innerHTML =
          artist + (album ? " - " + album : "") + "&nbsp;";
      },
      metadataTypes: ["icy", "ogg"],
      onPlay: () => {
        curve.classList.add("animate");
      },
      onStop: () => {
        playStop.setAttribute("data-status", "stopped");
        curve.classList.remove("animate");
      },
    })
  );

  const endpointSelect = player.querySelector(".player-controls > select");
  if (endpointSelect) {
    endpointSelect.addEventListener("change", () => {
      audioPlayer.switchEndpoint(endpointSelect.value, {});
    });
  }

  const playStop = player.querySelector(".player-controls > .play-stop");
  const play = playStop.querySelector("button:first-of-type");
  const stop = playStop.querySelector("button:last-of-type");
  const audioPlayer = audioPlayers[i];
  play.addEventListener("click", () => {
    playStop.setAttribute("data-status", "playing");
    for (let audioPlayer of audioPlayers) {
      audioPlayer.stop();
    }
    audioPlayer.play();
  });
  stop.addEventListener("click", () => {
    audioPlayer.stop();
  });
}

// Clear forms
const messageFrame = document.querySelector(".message-frame");
const forms = document.querySelectorAll("form");
for (let form of forms) {
  form.addEventListener("submit", () => {
    const onSent = function () {
      messageFrame.removeEventListener("load", onSent);
      if (messageFrame.contentDocument.title === "") {
        return;
      }
      for (const fileNameLabel of form.querySelectorAll(".record > p")) {
        fileNameLabel.innerText = "";
      }
      form.reset();
    };
    messageFrame.addEventListener("load", onSent);
  });
}

// Audio Recording
Number.prototype.toDigets = function (n = 2) {
  return (
    (this.toString().length < n ? "0".repeat(n - this.toString().length) : "") +
    this
  );
};
let recorderBlob;
let destroyMediaStream;
let recorderPlayerAudioElement;
const recorder = document.querySelector(".recorder");
const recorderRecord = recorder.querySelector(".main > .icon-main");
const recorderStop = recorder.querySelector(".controls > .icon-main");
const recorderClose = recorder.querySelector(".controls > button:first-child");
const recorderDone = recorder.querySelector(".controls > button:last-child");
const recorderPlayer = recorder.querySelector(".main > .player");
const recorderPlayerInfo = recorderPlayer.querySelector(".player-info");
const recorderPlayerPlayStop = recorderPlayer.querySelector(".play-stop");
const recorderPlayerSeek = recorderPlayer.querySelector('input[type="range"]');
const recorderPlayerPlay = recorderPlayerPlayStop.querySelector("button:first-child");
const recorderPlayerStop = recorderPlayerPlayStop.querySelector("button:last-child");

function formatTime(seconds) {
  return Math.floor(seconds / 60) + ":" + (seconds % 60).toDigets();
}

function recorderPlayerReset() {
  recorderPlayerPlayStop.setAttribute("data-status", "stopped");
  recorderPlayerInfo.setAttribute("data-position-current", "0:00");
  recorderPlayerSeek.value = 0;
}

function recorderDestroy() {
  destroyMediaStream?.();
  if (recorderPlayerAudioElement) recorderPlayerAudioElement.pause();
  recorderPlayerReset();
  recorder.setAttribute("data-status", "init");
  recorderDialog.close();
}
recorderClose.onclick = recorderDestroy;

const recorderBitrate = parseInt(recorder.getAttribute("data-bitrate"));
let recordingFormatMimeType;
for (const format of [
  "audio/ogg; codecs=opus",
  "audio/webm; codecs=opus",
  "audio/mp4",
]) {
  if (!MediaRecorder.isTypeSupported(format)) continue;
  recordingFormatMimeType = format;
  break;
}
function record() {
  if (recorder.getAttribute("data-status") === "recording") return;
  recorderPlayerAudioElement?.pause();
  if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
    navigator.mediaDevices.getUserMedia({
      audio: {
        echoCancellation: false,
        noiseSuppression: false,
        autoGainControl: recorder.getAttribute("data-enable-agc") === "on",
      },
    }).then((stream) => {
      const mediaRecorder = new MediaRecorder(stream, {
        mimeType: recordingFormatMimeType,
        audioBitsPerSecond: isNaN(recorderBitrate) ? undefined : recorderBitrate
      });
      let isDestroyed = false;
      destroyMediaStream = () => {
        isDestroyed = true;
        mediaRecorder.stop();
      };
      mediaRecorder.start();

      recorderStop.onclick = () => {
        mediaRecorder.stop();
      };
      let chunks = [];
      mediaRecorder.ondataavailable = (e) => {
        chunks.push(e.data);
      };
      mediaRecorder.onstart = () => {
        recorder.setAttribute("data-status", "recording");
      };
      mediaRecorder.onstop = () => {
        stream.getTracks().forEach((track) => track.stop());
        if (isDestroyed) return;
        recorderPlayerPlayStop.setAttribute("data-status", "stopped");

        recorderBlob = new Blob(chunks, { type: mediaRecorder.mimeType });
        chunks = [];
        recorderPlayerAudioElement = new Audio(window.URL.createObjectURL(recorderBlob));
        recorderPlayerSetupCallbacks(recorderPlayerAudioElement);
        recorderPlayerReset();
        recorderPlayerAudioElement.onloadedmetadata = () => {
          recorderPlayerSeek.setAttribute("max", recorderPlayerAudioElement.duration.toString());
          recorderPlayerInfo.setAttribute(
              "data-position-end",
              formatTime(Math.ceil(recorderPlayerAudioElement.duration))
          );
          recorder.setAttribute("data-status", "playable");
        };
      };
    }).catch((e) => {
      alert(e.message || e.name || JSON.stringify(e));
    });
  } else {
    alert("getUserMedia not supported on your browser!");
  }
}
recorderRecord.onclick = record;

function recorderPlayerSetupCallbacks(recorderPlayerAudioElement) {
  recorderPlayerPlay.onclick = () => {
    recorderPlayerAudioElement.play();
    recorderPlayerPlayStop.setAttribute("data-status", "playing");
  };
  recorderPlayerStop.onclick = () => {
    recorderPlayerAudioElement.pause();
    recorderPlayerPlayStop.setAttribute("data-status", "stopped");
  };
  recorderPlayerAudioElement.onended = recorderPlayerStop.onclick;
  recorderPlayerSeek.onchange = (event) => {
    recorderPlayerAudioElement.currentTime = event.target.value;
  };
  recorderPlayerAudioElement.ontimeupdate = (event) => {
    recorderPlayerInfo.setAttribute(
        "data-position-current",
        formatTime(Math.ceil(event.target.currentTime))
    );
    recorderPlayerSeek.value = event.target.currentTime;
  };
  recorderDone.onclick = () => {
    const file = new File([recorderBlob], "Snimka je priložena", {
      type: recorderBlob.type,
      lastModified: new Date().getTime(),
    });
    const container = new DataTransfer();
    container.items.add(file);
    recorderTargetFileInput.files = container.files;
    recorderTargetFileInput.onchange();

    recorderDestroy();
  };
}

let recorderTargetFileInput;
const recorderDialog = document.querySelector("#recorder");
const records = document.querySelectorAll(".record");
for (let index = 0; index < records.length; index++) {
  const record = records[index];
  const fileInput = record.querySelector('input[type="file"]');
  const recordButton = record.querySelector("button:first-of-type");
  const selectFileButton = record.querySelector("button:last-of-type");
  const fileLabel = document.querySelectorAll(".record > p")[index];

  recordButton.onclick = () => {
    recorderTargetFileInput = fileInput;
    recorderDialog.showModal();
    this.record();
  };
  selectFileButton.onclick = () => {
    fileInput.click();
  };
  fileInput.onchange = () => {
    fileLabel.innerText = fileInput.files[0].name;
  };
}
