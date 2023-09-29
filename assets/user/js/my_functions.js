// Monitor Image Changes on and input and image element 
function monitorImageChange(inputId, imageId) {
    var input = document.getElementById(inputId);
    var image = document.getElementById(imageId);
    var previousSrc = image.src;
  
    input.addEventListener('change', function() {
      if (input.files && input.files[0]) {
        var file = input.files[0];
        var reader = new FileReader();
  
        reader.onload = function(e) {
          if (isImageFile(file)) {
            image.setAttribute('data-previous-src', previousSrc);
            image.classList.add("img-changed");
            image.src = e.target.result;
          } else {
            // Not an image file, do something (e.g., display an error message)
            console.log("Selected file is not an image.");
          }
        };
  
        reader.readAsDataURL(file);
      } else {
        image.src = previousSrc;
        image.classList.remove("img-changed");
      }
    });
  }

function isImageFile(file) {
	return file.type.startsWith("image/");
}


