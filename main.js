function copyToClipboard(text) {
  navigator.clipboard.writeText(text).then(() => {
    alert("রেফারেল লিংক কপি করা হয়েছে!");
  }).catch(err => {
    console.error('Could not copy text: ', err);
  });
}
