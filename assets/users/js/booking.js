jQuery(document).ready(function ($) {
  // ("use strict");

  // Define variables for total price and total kg
  let totalPrice = 0;
  let totalKg = 0;
  let totalSpecialCharge = 0; // Initialize a variable to store the total special charge

  function getBookingStepTitle(stepLink) {
    let titleClone = stepLink.clone();
    titleClone.find('.number, i, .current-info, .audible').remove();
    return titleClone
      .text()
      .replace(/current step:\s*/i, '')
      .replace(/\s+/g, ' ')
      .trim();
  }

  function updateBookingStepTitle(form) {
    let currentStep = form.children('.steps').find('li.current a').first();
    let titleTarget = form.children('.booking-current-step-title');

    if (!currentStep.length || !titleTarget.length) {
      return;
    }

    titleTarget.text(getBookingStepTitle(currentStep));
  }

  function syncBookingStepperState(form) {
    let currentItem = form.children('.steps').find('li.current').first();
    let currentIndex = currentItem.index();

    if (currentIndex < 0) {
      return;
    }

    form.children('.steps').find('li').each(function (index) {
      $(this)
        .toggleClass('booking-step-past', index < currentIndex)
        .toggleClass('booking-step-future', index > currentIndex);
    });
  }

  function markBookingStepTransition(form) {
    clearTimeout(form.data('booking-step-transition-timeout'));
    form.addClass('booking-step-transitioning');
    form.data(
      'booking-step-transition-timeout',
      setTimeout(function () {
        form.removeClass('booking-step-transitioning');
      }, 420)
    );
  }

  function initBookingStepper(attempt) {
    let form = $('#booking_form.booking-wizard-form');
    let steps = form.children('.steps');
    let currentAttempt = attempt || 0;

    if (!form.length) {
      return;
    }

    if (!steps.length) {
      if (currentAttempt < 10) {
        setTimeout(function () {
          initBookingStepper(currentAttempt + 1);
        }, 50);
      }
      return;
    }

    syncBookingStepperState(form);

    if (!form.children('.booking-current-step-title').length) {
      steps.after('<div class="booking-current-step-title" aria-live="polite"></div>');
    }

    updateBookingStepTitle(form);

    form
      .off('click.bookingStepper')
      .on('click.bookingStepper', '.steps a, .actions a', function () {
        setTimeout(function () {
          markBookingStepTransition(form);
          syncBookingStepperState(form);
          updateBookingStepTitle(form);
        }, 80);
      });

    if (window.MutationObserver && !form.data('booking-stepper-observer')) {
      let observer = new MutationObserver(function () {
        markBookingStepTransition(form);
        syncBookingStepperState(form);
        updateBookingStepTitle(form);
      });

      steps.find('li').each(function () {
        observer.observe(this, {
          attributes: true,
          attributeFilter: ['class'],
        });
      });

      form.data('booking-stepper-observer', observer);
    }
  }

  function syncKycStepperState(form) {
    let currentItem = form.children('.steps').find('li.current').first();
    let currentIndex = currentItem.index();

    if (currentIndex < 0) {
      return;
    }

    form.children('.steps').find('li').each(function (index) {
      $(this)
        .toggleClass('kyc-step-past', index < currentIndex)
        .toggleClass('kyc-step-future', index > currentIndex);
    });
  }

  function syncKycActionLabels(form) {
    let currentIndex = form.children('.steps').find('li.current').first().index();
    let nextAction = form.children('.actions').find('a[href="#next"]');
    let finishAction = form.children('.actions').find('a[href="#finish"]');

    if (nextAction.length) {
      nextAction.text(currentIndex === 0 ? 'Continue' : 'Next');
    }

    if (finishAction.length) {
      finishAction.text('Submit');
    }
  }

  function initKycStepper(attempt) {
    let form = $('.kyc-wizard-form');
    let steps = form.children('.steps');
    let currentAttempt = attempt || 0;

    if (!form.length) {
      return;
    }

    if (!steps.length) {
      if (currentAttempt < 10) {
        setTimeout(function () {
          initKycStepper(currentAttempt + 1);
        }, 50);
      }
      return;
    }

    syncKycStepperState(form);
    syncKycActionLabels(form);

    form
      .off('click.kycStepper')
      .on('click.kycStepper', '.steps a, .actions a', function () {
        setTimeout(function () {
          syncKycStepperState(form);
          syncKycActionLabels(form);
        }, 80);
      });

    if (window.MutationObserver && !form.data('kyc-stepper-observer')) {
      let observer = new MutationObserver(function () {
        syncKycStepperState(form);
        syncKycActionLabels(form);
      });

      steps.find('li').each(function () {
        observer.observe(this, {
          attributes: true,
          attributeFilter: ['class'],
        });
      });

      form.data('kyc-stepper-observer', observer);
    }
  }

  function showMobileItemAddedFeedback(itemName) {
    if (!window.matchMedia('(max-width: 991px)').matches) {
      return;
    }

    let feedback = $('#booking-mobile-added-feedback');
    let summaryButton = $('#sign-in');

    if (!feedback.length) {
      feedback = $(
        '<div id="booking-mobile-added-feedback" class="booking-mobile-added-feedback" aria-live="polite"></div>'
      );
      $('body').append(feedback);
    }

    feedback.html(
      `<i class="ti ti-check"></i> ${itemName ? itemName : 'Item'} added. Tap View Summary to review.`
    );
    feedback.removeClass('is-visible');
    summaryButton.removeClass('booking-summary-pulse');

    window.requestAnimationFrame(function () {
      feedback.addClass('is-visible');
      summaryButton.addClass('booking-summary-pulse');
    });

    clearTimeout(feedback.data('hide-timeout'));
    feedback.data(
      'hide-timeout',
      setTimeout(function () {
        feedback.removeClass('is-visible');
        summaryButton.removeClass('booking-summary-pulse');
      }, 4600)
    );
  }

  function updateCategoryAdvisory() {
    const selectedCategory = $('#select1').val();
    const advisory = $('#category-advisory');

    if (!advisory.length) {
      return;
    }

    if (selectedCategory === 'Fish/Meat') {
      advisory.text(
        'Fish and meat travel at your own risk. ShareMyBag will not be responsible if the item goes bad during the traveller’s journey.'
      );
    } else if (selectedCategory === 'Medication') {
      advisory.text(
        'Medication security notice: please ensure all medications are properly packaged, clearly labelled, and compliant with airline and destination-country regulations.'
      );
    } else {
      advisory.text('');
    }
  }

  function getSelectedPaymentMethod() {
    return $('input[name="payment_method"]:checked').val() || 'paystack';
  }

  function syncPaymentMethod() {
    $('#payment_method').val(getSelectedPaymentMethod());
  }

  function isPremiumSmallCategory(category) {
    return (
      category === 'Documents/Small Electronics' ||
      category === 'Documents/Electronics' ||
      category === 'Gold'
    );
  }

  function isPremiumLaptopCategory(category) {
    return category === 'Laptop';
  }

  function isSpecialCategory(category) {
    return (
      category === 'Fish/Medicine' ||
      category === 'Fish/Meat' ||
      category === 'Medication'
    );
  }

  function isPieceCategory(category) {
    return isPremiumSmallCategory(category) || isPremiumLaptopCategory(category);
  }

  function calculateTravellerCommissionForSummary(selectedSpace) {
    let normalPayout = parseFloat($('#holdThisInfo').attr('normal_payout')) || 0;
    let specialPayout = parseFloat($('#holdThisInfo').attr('special_payout')) || normalPayout;
    let premiumSmallPayout =
      parseFloat($('#holdThisInfo').attr('premium_small_payout')) || normalPayout;
    let premiumLaptopPayout =
      parseFloat($('#holdThisInfo').attr('premium_laptop_payout')) ||
      premiumSmallPayout;
    let items = [];

    $('.select_item').each(function () {
      items.push({
        category: $(this).attr('category'),
        size: parseFloat($(this).attr('size')) || 0,
      });
    });

    return window.ShareMyBagPricing.calculateConfiguredTravellerCommission(items, {
      normal: normalPayout,
      special: specialPayout,
      premiumSmall: premiumSmallPayout,
      premiumLaptop: premiumLaptopPayout,
    });
  }

  // --- NEW: Function to update weight unit in the dropdown ---
  function updateWeightUnit(unit) {
    // Update the label text
    $('#weight-label').text(`Weight (${unit}) *`);

    // Update the options text in the dropdown
    $('#select2 option').each(function (index) {
      if (index > 0) {
        let value = $(this).val();
        // Remove old unit and append new unit
        let text = $(this).text();
        text = text.replace(/KG|Piece/g, '');
        $(this).text(`${value}${unit}`);
      }
    });

    // Store the current unit in the select element
    $('#select2').attr('data-unit', unit);

    // Update the unit in the summary panel
    $('#total-unit').text(unit);
  }

  // --- NEW: Event listener for category change ---
  $('#select1').change(function () {
    let selectedCategory = $(this).val();
    if (isPieceCategory(selectedCategory)) {
      updateWeightUnit('PC');
    } else {
      updateWeightUnit('KG');
    }
    updateCategoryAdvisory();
  });
  // --- END NEW LOGIC ---

  // NOTE: Calling updateBooking() once immediately to fix initial currency display
  initBookingStepper();
  initKycStepper();
  syncPaymentMethod();
  updateBooking();
  updateitems();
  updateCategoryAdvisory();

  $('#item-list')
    .children('.select_item')
    .each(function () {
      activeItem = $(this);
      activeItem.find('.delete').click(function () {
        activeItem.remove();
        updateitems();
        updateBooking();
      });
    });

  // Add an event listener to the "add-item" button
  $('button#add-me').click(function () {
    // Check the available space
    fetch(
      `${base_url}user_bookings/get_traveling_available_space/${$(
        'form#booking_form[key]'
      ).attr('key')}`
    )
      .then((response) => {
        return response.text();
      })
      .then((aSpace) => {
        let calculations = calculateBooking();
        let size = document.getElementById('select2').value;
        let currentBagSpace = parseFloat(aSpace) - calculations.selectedSpace;

        $('#availableSpace').text(`${currentBagSpace}KG`);

        if (size > currentBagSpace) {
          // Display error message
          toastr.error(
            `${size}KG exceeds available bag space(${currentBagSpace}KG)`,
            `Not Enough Bag Space`,
            {
              progressBar: true,
              timeOut: 5000,
            }
          );
          return false;
        }

        return parseFloat(currentBagSpace);
      })
      .then((aSpace) => {
        if (aSpace === false) {
          return false;
        }
        // Error message function
        function showError(error, element) {
          element.addClass('error');
          if (element.is('select:hidden')) {
            element.next('.nice-select').addClass('error');
          }
        }

        // Get the selected category, item name, and size
        const category = document.getElementById('select1').value;
        const price = $(`option[value = "${category}"]`).attr('data-price');
        const itemName = document.getElementById('item-name').value;
        const size = document.getElementById('select2').value;

        // --- NEW: Determine unit for the new item ---
        const unit = $('#select2').attr('data-unit'); // Get the current unit (KG or Piece)

        $('.error_msg_item').html('');

        if (parseFloat(size) > calculateBooking().currentAvailableSpace) {
          $('.error_msg_item').html(`
                    <div class="col-lg-12 text-danger">
                        Selected space (${parseFloat(
                          size
                        )}kg) cannot exceed available space (${
            calculateBooking().currentAvailableSpace
          }kg)
                    </div>`);
          return;
        }

        // Error messages for add button
        if (
          category.trim() == '' ||
          !parseFloat(size) ||
          itemName.trim() == ''
        ) {
          if (category.trim() == '') {
            showError(
              '<span for="select1" class="text_danger">Select a category</span>',
              $('#select1')
            );
          } else {
            $("span[for='select1']").remove();
            $('#select1').removeClass('error').next('.nice-select').removeClass('error');
          }

          if (itemName.trim() == '') {
            showError(
              '<span for="item-name" class="text_danger">Provide item name</span>',
              $('#item-name')
            );
          } else {
            $("span[for='#item-name']").remove();
            $('#item-name').removeClass('error');
          }

          if (!parseInt(size)) {
            showError(
              '<span for="select2" class="text_danger">Select item size</span>',
              $('#select2')
            );
          } else {
            $("span[for='select2']").remove();
            $('#select2').removeClass('error').next('.nice-select').removeClass('error');
          }
          return;
        } else {
          $('#item-options').find('span.text_danger').remove();
          $('#select1, #item-name, #select2').removeClass('error');
          $('#select1, #select2').next('.nice-select').removeClass('error');
        }

        // Calculate the kg based on the selected size
        let kg = parseFloat(size);

        // Define special charges for the two categories
        let specialCharge = isSpecialCategory(category) ? 10 : 0;

        // Create a new item element with the selected category, item name, size, price, special charge, and delete icon
        const newItem = document.createElement('div');
        let currencySymbol = $('#holdThisInfo').attr('symbol');
        newItem.classList.add('item');
        newItem.classList.add('select_item');
        newItem.setAttribute('category', category);
        newItem.setAttribute('itemName', itemName);
        newItem.setAttribute('size', kg);
        newItem.setAttribute('price', kg * price); // Set price attribute to unit price * size
        newItem.setAttribute('unitPrice', price);
        newItem.setAttribute('unit', unit); // --- NEW: Store the unit (KG or Piece) ---
        newItem.innerHTML = `
                    <span class="category">${category}</span>
                    <span class="name">${itemName}</span>
                    <span class="size">${size}${unit}</span>
                    <span class="price">${currencySymbol}${parseFloat(
          (kg * price).toFixed(2)
        )} </span>
                    <span class="delete" title="Delete item"><i class="ti ti-trash fs-4 text-danger"></i></span>
                `;

        // Add the new item element to the list
        document.getElementById('item-list').appendChild(newItem);
        showMobileItemAddedFeedback(itemName);

        // Add an event listener to the delete icon of the new item element
        newItem.querySelector('.delete').addEventListener('click', function () {
          // Remove the new item element from the list
          newItem.remove();

          updateitems();
          updateBooking();
          document.getElementById('special-charge-value').textContent =
            getSpecialCharge().toFixed(2);
        });

        // Clear the select and text fields
        document.getElementById('select1').selectedIndex = 0;
        document.getElementById('item-name').value = '';
        document.getElementById('select2').selectedIndex = 0;

        // Reset the unit display for the next item input in the form
        updateWeightUnit('KG');

        $("span[for='select1']").remove();
        $("span[for='item-name']").remove();
        $("span[for='select2']").remove();
        $('#item-options').find('span.error').remove();

        $('#availableSpace').text(`${parseFloat(aSpace) - size}KG`);
        updateitems();
        updateBooking();
        document.getElementById('special-charge-value').textContent =
          getSpecialCharge().toFixed(2); // Update special charge value
      })
      .catch(() => {
        toastr.error(
          'We could not confirm the available bag space. Please try again.',
          'Booking Error',
          {
            progressBar: true,
            timeOut: 5000,
          }
        );
      });
    return;
  });

  // Update booking on insurance select change
  $('#insuranceBox').change(function () {
    updateBooking();
  });

  $('input[name="payment_method"]').change(function () {
    syncPaymentMethod();
    updateBooking();
  });

  //For text to display while typing
  const receiverInput = document.getElementById('receiverName');
  const agentInput = document.getElementById('agentName');
  const receiverAddressInput = document.getElementById('receiverAddress');
  const agentaddressInput = document.getElementById('agentAddress');

  const receiverValue = document.getElementById('receiverNameValue');
  const agentValue = document.getElementById('agentNameValue');
  const receiverAddressValue = document.getElementById('receiverAddressValue');
  const agentaddressValue = document.getElementById('agentAddressValue');

  if (
    receiverInput &&
    agentInput &&
    receiverAddressInput &&
    agentaddressInput
  ) {
    receiverInput.addEventListener('input', (event) => {
      receiverValue.textContent = event.target.value;
    });

    agentInput.addEventListener('input', (event) => {
      agentValue.textContent = event.target.value;
    });

    receiverAddressInput.addEventListener('input', (event) => {
      receiverAddressValue.textContent = event.target.value;
    });

    agentaddressInput.addEventListener('input', (event) => {
      agentaddressValue.textContent = event.target.value;
    });
  }

  $('#capture-video').on('hidden.bs.modal', function () {
    // toggleCamera();
    closeCamera();
  });

  // Event listener for when the paragraph is clicked to open/close the camera
  const selfieParagraph = document.getElementById('selfie-paragraph');
  selfieParagraph
    ? selfieParagraph.addEventListener('click', function () {
        toggleCamera();
        $('#capture-video').modal('show');
      })
    : '';

  // Event listener for when the snap icon is clicked to capture the image
  const snapIcon = document.getElementById('snap-icon');
  snapIcon
    ? snapIcon.addEventListener('click', function () {
        let input = $(`#${$('#capture-video').attr('target-input')}`)[0];
        let image = $(`#${$('#capture-video').attr('target-img')}`)[0];

        captureImage(input, image);
        $('#capture-video').modal('hide');
      })
    : '';

  // Event listener for when the retake icon is clicked
  const retakeIcon = document.getElementById('retake-icon');
  retakeIcon ? retakeIcon.addEventListener('click', retakePicture) : '';

  //Image Preview
  function ImagePreview(input) {
    if (input.files && input.files[0]) {
      var reader = new FileReader();
      reader.onload = function (e) {
        $('#image_preview').attr('src', e.target.result);
      };
      reader.readAsDataURL(input.files[0]);
    }
  }

  $('#the_image').change(function () {
    ImagePreview(this);
    $('#image_preview_area').css('display', 'block');
  });

  $('#remove_image').click(function () {
    $('#the_image').val('');
    $('#image_preview_area').css('display', 'none');
  });

  //Image preview on update: toggle current and new image
  $('#the_image_on_update').change(function () {
    ImagePreview(this);
    $('#image_preview_area').css('display', 'block');
    $('#current_image_area').css('display', 'none');
    $('#change_image_text').css('display', 'none');
  });

  $('#remove_image').click(function () {
    $('#the_image_on_update').val('');
    $('#image_preview_area').css('display', 'none');
    $('#current_image_area').css('display', 'block');
    $('#change_image_text').css('display', 'block');
  });

  //Display account details based on account selected
  $(document).ready(function () {
    function handleSelectedAccChange() {
      var selectedAcc = $('#payment_acc_select');

      // Get the selected value
      var selectedValue = selectedAcc.val();

      var ubaAcc = $('#uba_account');
      var metroAcc = $('#metro_account');

      // Show the corresponding account column based on the selected value
      if (selectedValue === 'United Bank of Africa') {
        ubaAcc.removeClass('d-none');
        metroAcc.addClass('d-none');
      } else if (selectedValue === 'Metro Bank PLC') {
        metroAcc.removeClass('d-none');
        ubaAcc.addClass('d-none');
      } else if (selectedValue === '') {
        metroAcc.addClass('d-none');
        ubaAcc.addClass('d-none');
      }
    }

    $(document).ready(function () {
      // Call the function on load
      handleSelectedAccChange();

      var selectedAcc = $('#payment_acc_select');

      // Bind the function to the change event
      selectedAcc.on('change', handleSelectedAccChange);
    });
  });

  $('input.form-control').change(function () {
    $(this).siblings('span.error').css('display', 'none');
    $(this).removeClass('error');
  });

  $('select').change(function () {
    $(this).siblings('span.error').css('display', 'none');
    $(this).removeClass('error').next('.nice-select').removeClass('error');
  });

  function activateSelect() {
    $('div.nice-select').each(function () {
      if ($(this).attr('activate-select') != 'yes') {
        $(this).attr('activate-select', 'yes');
        $($(this))
          .find('ul.list')
          .children('.option')
          .click(function () {
            selectedVal = $(this).attr('data-value').trim();
            $(this)
              .parents('div.nice-select')
              .siblings('select')
              .val(selectedVal);
            $(this)
              .parents('div.nice-select')
              .siblings('span.error')
              .css('display', 'none');
          });
      }
    });
  }

  activateSelect();

  $('#bottom-wizard')
    .find('button.forward')
    .click(function () {
      activateSelect();
    });

  $('#bottom-wizard')
    .find('button.backward')
    .click(function () {
      activateSelect();
    });

  $('.reset_img_input').click(function () {
    const image = $(this).siblings('img');
    const inputRef = image.attr('id');
    const targetInput = $('input[holder="' + inputRef + '"]');

    if (!targetInput.length) {
      return;
    }

    targetInput.val('').removeClass('input-image-blob error');

    const inputEl = targetInput[0];
    if (inputEl.type === 'hidden') {
      const originalSrc = image.data('original-src') || image.attr('src');
      image.attr('src', originalSrc).removeClass('img-changed');
      $('.take-selfie[target-input="' + inputEl.id + '"]')
        .removeClass('kyc-selfie-retake')
        .html('<i class="ti ti-camera"></i><span>Take Selfie</span>');
      return;
    }

    const changeEvent = new Event('change');
    inputEl.dispatchEvent(changeEvent);
  });

  autoLoadPageHelpers();
});

function openCamera(input, img) {
  const videoContainer = document.getElementById('video-container');
  const videoPreview = document.getElementById('video-preview');
  const imagePreview = document.getElementById('image-preview');

  let captureModal = $('#capture-video');
  let input_count = $(`#${input}`).length;
  let img_count = $(`#${img}`).length;

  if (!input_count || !img_count) {
    return;
  } else {
    captureModal.attr('target-img', img);
    captureModal.attr('target-input', input);
  }

  // Only open the camera if it is not currently open
  if (!videoPreview.srcObject) {
    navigator.mediaDevices
      .getUserMedia({ video: true })
      .then((stream) => {
        videoPreview.srcObject = stream;
        videoContainer.style.display = 'block';
        videoPreview.style.display = 'block';
        imagePreview.style.display = 'none';
        captureModal.modal('show');
      })
      .catch(() => {});
  }
}

// Function to close camera
function closeCamera() {
  const videoContainer = document.getElementById('video-container');
  const videoPreview = document.getElementById('video-preview');
  const imagePreview = document.getElementById('image-preview');

  let captureModal = $('#capture-video');

  // Check if the camera is open by checking if srcObject has a stream
  if (videoPreview.srcObject) {
    // Stop video stream
    const stream = videoPreview.srcObject;
    const tracks = stream.getTracks();
    tracks.forEach((track) => track.stop());

    videoPreview.srcObject = null; // Clear the video source to release the camera
    videoContainer.style.display = 'none'; // Optionally hide video container
    videoPreview.style.display = 'none';
    imagePreview.style.display = 'block';
  }

  captureModal.removeAttr('target-img');
  captureModal.removeAttr('target-input');
}

// Function to capture the image from the webcam
let isCameraOpen = false;

// Function to open/close the camera
function toggleCamera() {
  const selfieParagraph = document.getElementById('selfie-paragraph');
  const videoContainer = document.getElementById('video-container');
  const actionButtons = document.getElementById('action-buttons');
  const videoPreview = document.getElementById('video-preview');
  const imagePreview = document.getElementById('image-preview');
  videoPreview.style.display = 'block';
  imagePreview.style.display = 'none';

  if (!isCameraOpen) {
    // Access the user's webcam
    navigator.mediaDevices
      .getUserMedia({ video: true })
      .then((stream) => {
        videoPreview.srcObject = stream;
      })
      .catch(() => {});

    selfieParagraph.textContent = 'Close camera';
    videoContainer.style.display = 'block';
    imagePreview.style.display = 'none';
    isCameraOpen = true;
  } else {
    // Stop video stream
    const stream = videoPreview.srcObject;
    const tracks = stream.getTracks();
    tracks.forEach((track) => track.stop());

    selfieParagraph.textContent = 'Click here to take a selfie!';
    videoContainer.style.display = 'block';
    imagePreview.style.display = 'block';
    isCameraOpen = false;
  }
}

// Function to capture the image from the webcam
function captureImage(input = null, image = null) {
  const videoPreview = document.getElementById('video-preview');
  const modalPreview = document.getElementById('image-preview');
  const imagePreview = image ?? document.getElementById('selfie_holder');
  const inputEl = input ?? document.getElementById('image-input');

  if (!videoPreview || !imagePreview || !inputEl || !videoPreview.videoWidth) {
    return;
  }

  // Create a canvas element
  const canvas = document.createElement('canvas');
  canvas.width = videoPreview.videoWidth;
  canvas.height = videoPreview.videoHeight;
  const context = canvas.getContext('2d');
  context.drawImage(videoPreview, 0, 0, canvas.width, canvas.height);

  // Convert the canvas image to a data URL
  const imageDataURL = canvas.toDataURL();
  inputEl.value = imageDataURL;

  inputEl.classList.add('input-image-blob');

  // Display the captured image in the preview box
  if (!$(imagePreview).data('original-src')) {
    $(imagePreview).data('original-src', imagePreview.src);
  }
  imagePreview.src = imageDataURL;
  imagePreview.classList.add('img-changed');

  if (modalPreview) {
    modalPreview.src = imageDataURL;
    modalPreview.style.display = 'block';
  }

  $('.take-selfie[target-input="' + inputEl.id + '"]')
    .addClass('kyc-selfie-retake')
    .html('<i class="ti ti-camera-rotate"></i><span>Retake Selfie</span>');

  // Hide the video preview
  videoPreview.style.display = 'none';
}

// Function to retake the picture
function retakePicture() {
  const captureModal = $('#capture-video');
  const videoPreview = document.getElementById('video-preview');
  const imagePreview = document.getElementById('image-preview');

  if (!videoPreview || !navigator.mediaDevices) {
    return;
  }

  if (videoPreview.srcObject) {
    videoPreview.srcObject.getTracks().forEach((track) => track.stop());
    videoPreview.srcObject = null;
  }

  navigator.mediaDevices
    .getUserMedia({ video: true })
    .then((stream) => {
      videoPreview.srcObject = stream;
      videoPreview.style.display = 'block';
      if (imagePreview) {
        imagePreview.style.display = 'none';
      }
      captureModal.modal('show');
    })
    .catch(() => {});
}

// Monitor Image Changes on and input and image element
function monitorImageChange(inputId, imageId) {
  var input = document.getElementById(inputId);
  var image = document.getElementById(imageId);
  var previousSrc = image.src;

  input.addEventListener('change', function () {
    if (input.files && input.files[0]) {
      var file = input.files[0];
      var reader = new FileReader();

      reader.onload = function (e) {
        if (isImageFile(file)) {
          image.setAttribute('data-previous-src', previousSrc);
          image.classList.add('img-changed');
          image.src = e.target.result;
        } else {
          // Not an image file, do something (e.g., display an error message)
          return;
        }
      };

      reader.readAsDataURL(file);
    } else {
      image.src = previousSrc;
      image.classList.remove('img-changed');
    }
    input.classList.add('img-change-monitor-init');
  });
}

//Update the add in the item field
function updateitems() {
  var items = [];

  $('.select_item').each(function (index, element) {
    let item = {
      category: $(this).attr('category'),
      item_name: $(this).attr('itemName'),
      size: $(this).attr('size'),
      price: $(this).attr('price'),
      unit_price: $(this).attr('unitPrice'),
      unit: $(this).attr('unit'), // Capture unit from item element
    };
    items.push(item);
  });
  items.length === 0
    ? $('#items_input').val('')
    : $('#items_input').val(JSON.stringify(items));
  $('#items_input').trigger('change');
  return items;
}

function getSelectedPaymentMethod() {
  return $('input[name="payment_method"]:checked').val() || 'paystack';
}

function syncPaymentMethod() {
  $('#payment_method').val(getSelectedPaymentMethod());
}

function isPremiumSmallCategory(category) {
  return (
    category === 'Documents/Small Electronics' ||
    category === 'Documents/Electronics' ||
    category === 'Gold'
  );
}

function isPremiumLaptopCategory(category) {
  return category === 'Laptop';
}

function isSpecialCategory(category) {
  return (
    category === 'Fish/Medicine' ||
    category === 'Fish/Meat' ||
    category === 'Medication'
  );
}

function calculateTravellerCommissionForSummary(selectedSpace) {
  let normalPayout = parseFloat($('#holdThisInfo').attr('normal_payout')) || 0;
  let specialPayout = parseFloat($('#holdThisInfo').attr('special_payout')) || normalPayout;
  let premiumSmallPayout =
    parseFloat($('#holdThisInfo').attr('premium_small_payout')) || normalPayout;
  let premiumLaptopPayout =
    parseFloat($('#holdThisInfo').attr('premium_laptop_payout')) ||
    premiumSmallPayout;
  let items = [];

  $('.select_item').each(function () {
    items.push({
      category: $(this).attr('category'),
      size: parseFloat($(this).attr('size')) || 0,
    });
  });

  return window.ShareMyBagPricing.calculateConfiguredTravellerCommission(items, {
    normal: normalPayout,
    special: specialPayout,
    premiumSmall: premiumSmallPayout,
    premiumLaptop: premiumLaptopPayout,
  });
}

$('#items_input').change(function () {
  let val = $(this).val().trim();
  let category = $('select[name="category"]');
  let size = $('select[name="size"]');
  let item = $('input[name="item"]');

  if (val !== '') {
    category.removeClass('required');
    size.removeClass('required');
    item.removeClass('required');
  } else {
    category.addClass('required');
    size.addClass('required');
    item.addClass('required');
  }
});

//Calculate selected items
function calculateBooking() {
  let initialAvailableSpace = parseFloat($('.available_space').attr('space'));
  let serviceCharge = parseFloat($('.service_charge').attr('charge'));
  let selectedSpace = 0;
  let selectedPrice = 0;
  let insurance = 0;
  let paymentMethod = $('input[name="payment_method"]:checked').val() || 'paystack';

  // --- UPDATED CURRENCY ATTRIBUTES ---
  let currency = $('#holdThisInfo').attr('currency');
  let onePound = $('#holdThisInfo').attr('one_pound');
  let oneDollar = $('#holdThisInfo').attr('one_dollar');
  // --- END UPDATED CURRENCY ATTRIBUTES ---

  $('.select_item').each(function (index, element) {
    selectedSpace += parseFloat($(this).attr('size'));
    selectedPrice += parseFloat($(this).attr('price'));
  });

  // The logic for firstSelectedPrice and secondSelectedPrice seems deprecated
  // or incomplete and is generally avoided in production code unless necessary.
  // We'll keep the original structure but note the currency difference.
  firstSelectedPrice =
    currency == 'GBP'
      ? 50000 / parseFloat(onePound)
      : currency == 'CAD'
      ? 50000 / parseFloat(oneDollar)
      : 50000;

  secondSelectedPrice =
    currency == 'GBP'
      ? 100000 / parseFloat(onePound)
      : currency == 'CAD'
      ? 100000 / parseFloat(oneDollar)
      : 100000;

  insuranceOne =
    currency == 'GBP'
      ? 1500 / parseFloat(onePound)
      : currency == 'CAD'
      ? 1500 / parseFloat(oneDollar)
      : 1500;

  insuranceTwo =
    currency == 'GBP'
      ? 3000 / parseFloat(onePound)
      : currency == 'CAD'
      ? 3000 / parseFloat(oneDollar)
      : 3000;

  // Get the selected insurance value from the select element
  let selectedInsurance = parseFloat(
    $('#insuranceBox option:selected').data('insurance')
  );

  // Use the selected insurance value directly
  insurance = selectedInsurance ? selectedInsurance : 0;

  let currentAvailableSpace = initialAvailableSpace - selectedSpace;
  let specialCharge = getSpecialCharge();
  let subTotal = serviceCharge + selectedPrice;
  let baseTotal = subTotal + insurance + specialCharge;
  let travellerCommission = calculateTravellerCommissionForSummary(selectedSpace);
  let platformCommission = Math.max(
    0,
    baseTotal - travellerCommission - serviceCharge - insurance
  );
  let vatBase = platformCommission + serviceCharge;
  let vat = paymentMethod === 'paystack' ? (7.5 / 100) * vatBase : 0;
  let totalAmount = baseTotal + vat;
  let calculatedValues = {
    initialAvailableSpace: initialAvailableSpace,
    selectedSpace: parseFloat(selectedSpace.toFixed(2)),
    selectedPrice: parseFloat(selectedPrice.toFixed(2)),
    totalAmount: parseFloat(totalAmount.toFixed(2)),
    baseTotal: parseFloat(baseTotal.toFixed(2)),
    subTotal: parseFloat(subTotal.toFixed(2)),
    vat: parseFloat(vat.toFixed(2)),
    vatBase: parseFloat(vatBase.toFixed(2)),
    platformCommission: parseFloat(platformCommission.toFixed(2)),
    travellerCommission: parseFloat(travellerCommission.toFixed(2)),
    currentAvailableSpace: currentAvailableSpace,
    serviceCharge: parseFloat(serviceCharge.toFixed(2)),
    insurance: parseFloat(insurance.toFixed(2)),
    specialCharge: parseFloat(specialCharge.toFixed(2)),
    paymentMethod: paymentMethod,
    currency: currency,
    onePound: onePound,
    oneDollar: oneDollar, // Added oneDollar
  };
  $('#price_calculations').val(JSON.stringify(calculatedValues));
  return calculatedValues;
}

// Update prices in summary
function updateBooking() {
  $('.available_space').html(`${calculateBooking().currentAvailableSpace}KG`);
  $('#total-kg').html(`${calculateBooking().selectedSpace}`);
  $('#total-kgs').html(`${calculateBooking().selectedSpace}`);

  // --- NEW: Update the summary unit based on the last added item's category/unit state ---
  let unit = $('#select2').attr('data-unit');
  $('#total-unit').text(unit);

  // Update unit for items already in the list
  $('#item-list .select_item').each(function () {
    let itemCategory = $(this).attr('category');
    let itemUnit = $(this).attr('unit'); // Use the unit attribute stored when item was added
    $(this)
      .find('.size')
      .text($(this).attr('size') + itemUnit);
  });
  // --- END NEW: Update unit logic ---

  // --- UPDATED CURRENCY DISPLAY LOGIC ---
  let currency = calculateBooking().currency;
  let totalAmount = calculateBooking().totalAmount;
  let subTotal = calculateBooking().subTotal;
  let insurance = calculateBooking().insurance;
  let vat = calculateBooking().vat;
  let specialCharge = getSpecialCharge();

  let displaySymbol = $('#holdThisInfo').attr('symbol');

  $('#total-price').html(
    `${displaySymbol}${totalAmount.toFixed(2).toLocaleString()}`
  );
  $('#sub-total').html(
    `${displaySymbol}${subTotal.toFixed(2).toLocaleString()}`
  );
  $('#insurance-value').html(
    `${displaySymbol}${insurance.toFixed(2).toLocaleString()}`
  );
  $('#vat-value').html(`${displaySymbol}${vat.toFixed(2).toLocaleString()}`);
  $('#special-charge-value').html(
    `${displaySymbol}${specialCharge.toFixed(2)}`
  );
  $('.vat_row').toggle(vat > 0);

  // Explicitly update Service Charge with symbol (Fixes missing symbol on load/update)
  let serviceChargeValue = parseFloat($('.service_charge').attr('charge'));
  $('.service_charge span span').html(
    `${displaySymbol}${serviceChargeValue.toFixed(2)}`
  );

  // Determine the correct total amount to show in the large summary area
  let largeTotalDisplay = '';
  if (currency === 'CAD') {
    // Show CAD amount with $ symbol
    largeTotalDisplay =
      $('#holdThisInfo').attr('dollar_sign') +
      totalAmount.toFixed(2).toLocaleString();
  } else {
    // Show GBP amount with £ symbol
    largeTotalDisplay =
      $('#holdThisInfo').attr('pound_sign') +
      totalAmount.toFixed(2).toLocaleString();
  }

  // Set the value for the large display on the summary page
  $('#totalAmountDisplay').html(largeTotalDisplay);

  // --- END UPDATED CURRENCY DISPLAY LOGIC ---
}

// get special charge
function getSpecialCharge() {
  let specialCharge = 0;
  // Note: This reflects the customer-facing special charge displayed in the summary
  let items = $('#items_input').val();
  if (items) {
    items = JSON.parse(items);
    let categories = items.map((item) => item.category);
    if (
      categories.some((category) => isSpecialCategory(category))
    ) {
      specialCharge += 10; // Extra £10 displayed to user
    }
  }
  return specialCharge;
}

// function getSpecialCharge() {
//   let specialCharge = 0;
//   let specialCharges = {
//     "Fish/Medicine": 10, // Special charge for this category
//     // "Documents/Electronics": 15, // Special charge for this category
//   };
//   let items = $("#items_input").val();
//   if (items) {
//     items = JSON.parse(items);
//     let categories = items.map((item) => item.category);
//     if (categories.includes("Fish/Medicine")) {
//       specialCharge += 10;
//     }
//     // if (categories.includes("Documents/Electronics")) {
//     //   specialCharge += 15;
//     // }
//   }
//   return specialCharge;
// }

// Function to convert Data URI to Blob
function dataURItoBlob(dataURI) {
  // Split the Data URI into metadata and data parts
  var parts = dataURI.split(',');
  var metadata = parts[0]; // e.g., "data:image/png;base64"
  var data = parts[1]; // e.g., "iVBORw0KGg...."

  // Extract the mime type from the metadata
  var mimeType = metadata.split(';')[0].split(':')[1];

  // Convert base64-encoded data to a Blob object
  var byteCharacters = atob(data);
  var byteArrays = [];
  for (var i = 0; i < byteCharacters.length; i++) {
    byteArrays.push(byteCharacters.charCodeAt(i));
  }
  var byteArray = new Uint8Array(byteArrays);
  var blob = new Blob([byteArray], { type: mimeType });

  return blob;
}

// Get extension form raw image data
function getFileExtensionFromMimeType(mimeType) {
  const mimeTypesMap = {
    'image/jpeg': 'jpg',
    'image/png': 'png',
    'image/gif': 'gif',
    'image/bmp': 'bmp',
    'image/webp': 'webp',
    'image/svg+xml': 'svg',
    'application/pdf': 'pdf',
    'application/msword': 'doc',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
      'docx',
    'application/vnd.ms-excel': 'xls',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': 'xlsx',
    'application/vnd.ms-powerpoint': 'ppt',
    'application/vnd.openxmlformats-officedocument.presentationml.presentation':
      'pptx',
    'text/plain': 'txt',
    'text/html': 'html',
    'text/css': 'css',
    'application/json': 'json',
    'application/xml': 'xml',
    // Add more MIME types and their corresponding extensions as needed
  };

  // Extract MIME type without any parameters
  const mimeTypeWithoutParams = mimeType.split(';')[0].trim();

  // Search for matching MIME type in the map
  for (const type in mimeTypesMap) {
    if (mimeTypesMap.hasOwnProperty(type) && type === mimeTypeWithoutParams) {
      return mimeTypesMap[type];
    }
  }

  // If no matching extension is found, return null or handle the case as needed
  return null;
}

function isImageFile(file) {
  return file.type.startsWith('image/');
}
