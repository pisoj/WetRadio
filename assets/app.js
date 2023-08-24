// Live players
const players = document.querySelectorAll("#live > .player");
const audioPlayers = [];
for (let i = 0; i < players.length; i++) {
  const player = players[i];
  const endpoints = player.querySelector(".endpoints");
  audioPlayers.push(
    new IcecastMetadataPlayer(JSON.parse(endpoints.innerText), {
      endpointOrder: endpoints.getAttribute("data-order"),
      onMetadata: (metadata) => {
        const titleElement = player.querySelector(".info > h4 > b");
        const subtitleElement = player.querySelector(".info > p");
        const info = metadata.StreamTitle.replace(/\s*-\s*/g, "-").split("-");
        const title = info[1] ? info[1] : info[0];
        const artist = info[1] ? info[0] : "";
        const album = info.slice(2).join(" - ");
        titleElement.innerHTML = title + "&nbsp;";
        subtitleElement.innerHTML =
          artist + (album ? " - " + album : "") + "&nbsp;";
      },
      metadataTypes: ["icy"],
      onStop: () => {
        playStop.setAttribute("data-status", "stopped");
      },
    })
  );

  const endpointSelect = player.querySelector(".controls > select");
  endpointSelect.addEventListener("change", () => {
    audioPlayer.switchEndpoint(endpointSelect.value, {});
  });

  const playStop = player.querySelector(".controls > .play-stop");
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
let amr;
let recorderPlayerTimeUpdateIntervalId;
const recorder = document.querySelector(".recorder");
const recorderRecord = recorder.querySelector(".main > .icon-main");
const recorderStop = recorder.querySelector(".controls > .icon-main");
const recorderClose = recorder.querySelector(".controls > button:first-child");
const recorderDone = recorder.querySelector(".controls > button:last-child");
const recorderPlayer = recorder.querySelector(".main > .player");
const recorderPlayerInfo = recorderPlayer.querySelector(".info");
const recorderPlayerPlayStop = recorderPlayer.querySelector(".play-stop");
const recorderPlayerSeek = recorderPlayer.querySelector('input[type="range"]');
const recorderPlayerPlay = recorderPlayerPlayStop.querySelector(":first-child");
const recorderPlayerStop = recorderPlayerPlayStop.querySelector(":last-child");

function formatTime(seconds) {
  return Math.floor(seconds / 60) + ":" + (seconds % 60).toDigets();
}

function recorderPlayerCurrentTimeUpdate() {
  recorderPlayerInfo.setAttribute(
    "data-position-current",
    formatTime(Math.ceil(amr.getCurrentPosition()))
  );
  recorderPlayerSeek.value = amr.getCurrentPosition();
}

function recorderPlayerReset() {
  recorderPlayerPlayStop.setAttribute("data-status", "stopped");
  recorderPlayerInfo.setAttribute("data-position-current", "0:00");
  recorderPlayerSeek.value = 0;
}

function setRecorderPlayerTimeUpdateInterval() {
  if (amr) {
    recorderPlayerCurrentTimeUpdate();
  }
  recorderPlayerTimeUpdateIntervalId = setInterval(() => {
    if (amr) {
      recorderPlayerCurrentTimeUpdate();
    }
  }, 1000);
}

function recorderDestroy() {
  amr.destroy();
  amr = null;
  clearInterval(recorderPlayerTimeUpdateIntervalId);
  recorderPlayerReset();
}

function setupRecorderListeners() {
  amr.onPlay(() => {
    setRecorderPlayerTimeUpdateInterval();
    recorderPlayerPlayStop.setAttribute("data-status", "playing");
  });
  amr.onResume(() => {
    setRecorderPlayerTimeUpdateInterval();
    recorderPlayerPlayStop.setAttribute("data-status", "playing");
  });
  amr.onStop(() => {
    recorderPlayerPlayStop.setAttribute("data-status", "stopped");
    clearInterval(recorderPlayerTimeUpdateIntervalId);
  });
  amr.onPause(() => {
    recorderPlayerPlayStop.setAttribute("data-status", "stopped");
    clearInterval(recorderPlayerTimeUpdateIntervalId);
  });
  amr.onStartRecord(() => {
    recorder.setAttribute("data-status", "recording");
  });
  amr.onFinishRecord(() => {
    recorderPlayerReset();
    recorderPlayerSeek.setAttribute("max", amr.getDuration());
    recorderPlayerInfo.setAttribute(
      "data-position-end",
      formatTime(Math.ceil(amr.getDuration()))
    );
    recorder.setAttribute("data-status", "playable");
  });
}

recorderClose.onclick = () => {
  recorderWindow.removeAttribute("open");
  recorderDestroy();
  recorder.setAttribute("data-status", "init");
};
recorderDone.onclick = () => {
  recorderWindow.removeAttribute("open");

  const file = new File([amr.getBlob()], "recording.amr", {
    type: "audio/amr",
    lastModified: new Date().getTime(),
  });
  const container = new DataTransfer();
  container.items.add(file);
  recorderTargetFileInput.files = container.files;

  recorderDestroy();
  recorder.setAttribute("data-status", "init");
};
recorderRecord.onclick = () => {
  if (amr && amr.isRecording()) return;
  if (amr) amr.stop();
  amr = new BenzAMRRecorder();
  setupRecorderListeners();
  amr
    .initWithRecord()
    .then(() => {
      amr.startRecord();
    })
    .catch(function (e) {
      alert(e.message || e.name || JSON.stringify(e));
    });
};
recorderStop.onclick = () => amr.finishRecord();
recorderPlayerPlay.onclick = () => amr.playOrResume();
recorderPlayerStop.onclick = () => amr.pause();
recorderPlayerSeek.onchange = (event) => {
  amr.setPosition(event.target.value);
  recorderPlayerCurrentTimeUpdate();
};

let recorderTargetFileInput;
const recorderWindow = document.querySelector("#recorder");
const records = document.querySelectorAll(".record");
for (let index = 0; index < records.length; index++) {
  const record = records[index];
  const fileInput = record.querySelector('input[type="file"]');
  const recordButton = record.querySelector("button:first-of-type");
  const selectFileButton = record.querySelector("button:last-of-type");
  const fileLabel = document.querySelectorAll(".record + p")[index];

  recordButton.onclick = () => {
    recorderTargetFileInput = fileInput;
    recorderWindow.setAttribute("open", "");
  };
  selectFileButton.onclick = () => {
    fileInput.click();
  };
}
