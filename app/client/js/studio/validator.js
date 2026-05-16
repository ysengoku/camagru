export async function validateUploadedFile(file, maxFileSize) {
  if (!file) {
    return 'No file selected.';
  }
  if (file.size > maxFileSize) {
    return (
      'Too large file. Maximum allowed size is ' +
      maxFileSize / (1024 * 1024) +
      ' MB.'
    );
  }

  const allowedTypes = ['image/png', 'image/jpeg'];
  if (!allowedTypes.includes(file.type)) {
    return 'Only PNG or JPEG format is supported.';
  }

  if (!(await validateFileSignature(file))) {
    return 'Invalid file content. Please upload a valid PNG or JPEG image.';
  }

  return null;
}

function validateFileSignature(file) {
  return new Promise((resolve) => {
    const reader = new FileReader();
    reader.onload = (e) => {
      const arr = new Uint8Array(e.target.result).subarray(0, 4);
      let header = '';
      for (let i = 0; i < arr.length; i++) {
        header += arr[i].toString(16);
      }

      const isPNG = header.startsWith('89504e47');
      const isJPEG = header.startsWith('ffd8ff');

      resolve(isPNG || isJPEG);
    };
    reader.readAsArrayBuffer(file.slice(0, 4));
  });
}
