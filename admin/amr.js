function float32ArrayToWave(samples, sampleRate) {
  const buffer = new ArrayBuffer(44 + samples.length * 2);
  const view = new DataView(buffer);

  // Write WAV header
  writeString(view, 0, "RIFF");
  view.setUint32(4, 32 + samples.length * 2, true);
  writeString(view, 8, "WAVE");
  writeString(view, 12, "fmt ");
  view.setUint32(16, 16, true);
  view.setUint16(20, 1, true);
  view.setUint16(22, 1, true);
  view.setUint32(24, sampleRate, true);
  view.setUint32(28, sampleRate * 2, true);
  view.setUint16(32, 2, true);
  view.setUint16(34, 16, true);
  writeString(view, 36, "data");
  view.setUint32(40, samples.length * 2, true);

  // Write audio data
  floatTo16BitPCM(view, 44, samples);

  // Create Blob
  const blob = new Blob([view], { type: "audio/wav" });

  return blob;
}

function writeString(view, offset, string) {
  for (let i = 0; i < string.length; i++) {
    view.setUint8(offset + i, string.charCodeAt(i));
  }
}

function floatTo16BitPCM(output, offset, input) {
  for (let i = 0; i < input.length; i++, offset += 2) {
    const sample = Math.max(-1, Math.min(1, input[i]));
    output.setInt16(
      offset,
      sample < 0 ? sample * 0x8000 : sample * 0x7fff,
      true
    );
  }
}

function supportAmr() {
  for (const audio of document.querySelectorAll('audio[src$=".amr"]')) {
    const amr = new BenzAMRRecorder();
    amr.initWithUrl(audio.src).then(function () {
      const blob = float32ArrayToWave(amr.ee, amr.$ ? l.getCtxSampleRate() : 8e3);
      audio.src = URL.createObjectURL(blob);
    });
  }
}
