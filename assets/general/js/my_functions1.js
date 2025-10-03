// Monitor Image Changes on and input and image element
function monitorImageChange(inputId, imageId) {
  var input = document.getElementById(inputId);
  var image = document.getElementById(imageId);
  var previousSrc = image.src;

  input.addEventListener("change", function () {
    if (input.files && input.files[0]) {
      var file = input.files[0];
      var reader = new FileReader();

      reader.onload = function (e) {
        if (isImageFile(file)) {
          image.setAttribute("data-previous-src", previousSrc);
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

//universal form ajax
function submitFormAjax(form) {
  let action = form.hasAttribute("action")
    ? form.getAttribute("action")
    : false;
  let redirect = form.hasAttribute("redirect")
    ? form.getAttribute("redirect")
    : false;
  let reset = form.hasAttribute("reset") ? true : false;

  if (action) {
    // If action
    let form_data = new FormData(form);
    // Get raw image input
    $(".input-image-blob").each(function () {
      let blob = dataURItoBlob($(this).val()); // Updated ID here
      let ext = getFileExtensionFromMimeType(blob.type);
      form_data.append($(this).attr("name"), blob, "image." + ext);
    });

    showFormLoader();

    // Sending http request
    $.ajax({
      url: action, // Replace with your server endpoint
      type: "POST",
      data: form_data,
      dataType: "json",
      processData: false, // Required for form_data to work without URL-encoding data
      contentType: false, // Prevent jQuery from setting content-type header
      success: function (res) {
        if (res.status) {
          // Display success message
          toastr.success(res.msg, res?.title ?? "Success", {
            progressBar: true,
            timeOut: res?.msg_timeout ?? 5000,
          });

          // Reset form fields
          if (reset) {
            form.reset();
          }

          // Redirect
          if (redirect) {
            setTimeout(function () {
              $(location).attr("href", redirect);
            }, res?.msg_timeout ?? 4000);
            return;
          }

          // Redirect server
          if (res?.redirect) {
            setTimeout(function () {
              $(location).attr("href", res?.redirect);
            }, res?.msg_timeout ?? 4000);
            return;
          }

          setTimeout(() => {
            hideFormLoader();
          }, 700);
        } else {
          setTimeout(() => {
            hideFormLoader();
          }, 700);
          // Display error message
          toastr.error(res.msg, res?.title ?? "Error", {
            progressBar: true,
            timeOut: res?.msg_timeout ?? 5000,
          });
        }
      },
      error: function (xhr, status, error) {
        setTimeout(() => {
          hideFormLoader();
        }, 700);
        // Handle error response
        toastr.error(error, "Error", {
          progressBar: true,
          timeOut: 4000,
        });
        console.log("<p>An error occurred: " + error + "</p>");
      },
    });
  }

  return;
}

// show form loader
function showFormLoader() {
  let loader = document.getElementById("transparent-loader");

  if (loader) {
    loader.style.display = "grid";
  }
}

// hide form loader
function hideFormLoader() {
  let loader = document.getElementById("transparent-loader");

  if (loader) {
    loader.style.display = "none";
  }
}

// form validation with wizard
$(".form-wizard-ajax").each(function () {
  let advanced_form = $(this).show();

  advanced_form
    .steps({
      headerTag: "h3",
      bodyTag: "fieldset",
      transitionEffect: "slideLeft",
      onStepChanging: function (event, currentIndex, newIndex) {
        // Allways allow previous action even if the current form is not valid!
        if (currentIndex > newIndex) {
          return true;
        }
        // Forbid next action on "Warning" step if the user is to young
        if (newIndex === 3 && Number($("#age-2").val()) < 18) {
          return false;
        }
        // Needed in some cases if the user went back (clean up)
        if (currentIndex < newIndex) {
          // To remove error styles
          advanced_form.find(".body:eq(" + newIndex + ") label.error").remove();
          advanced_form
            .find(".body:eq(" + newIndex + ") .error")
            .removeClass("error");
        }
        advanced_form.validate().settings.ignore = ":disabled,:hidden";
        return advanced_form.valid();
      },
      onStepChanged: function (event, currentIndex, priorIndex) {
        // Used to skip the "Warning" step if the user is old enough.
        if (currentIndex === 2 && Number($("#age-2").val()) >= 18) {
          advanced_form.steps("next");
        }
        // Used to skip the "Warning" step if the user is old enough and wants to the previous step.
        if (currentIndex === 2 && priorIndex === 3) {
          advanced_form.steps("previous");
        }
        autoLoadPageHelpers();
      },
      onFinishing: function (event, currentIndex) {
        advanced_form.validate().settings.ignore = ":disabled";
        return advanced_form.valid();
      },
      onFinished: function (event, currentIndex) {
        // console.log("Submitted!", event.target, event);
        submitFormAjax(event.target);
      },
      onInit: function (event, currentIndex) {
        autoLoadPageHelpers();
      },
    })
    .validate({
      errorPlacement: function errorPlacement(error, element) {
        element.before(error);
      },
      rules: {
        confirm: {
          equalTo: "#password-2",
        },
      },
    });
});

// form validation
$(".form-ajax").each(function () {
  $(this).submit(function (e) {
    e.preventDefault();
    submitFormAjax(e.target);
  });
});

// password toggle
const toggleButtons = document.querySelectorAll(".toggle-password");

toggleButtons.forEach((button) => {
  button.addEventListener("click", function () {
    const targetId = this.getAttribute("data-target");
    const input = document.getElementById(targetId);
    const type =
      input.getAttribute("type") === "password" ? "text" : "password";
    input.setAttribute("type", type);
    this.querySelector("i").classList.toggle("fa-eye");
    this.querySelector("i").classList.toggle("fa-eye-slash");
  });
});
