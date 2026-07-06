jQuery(document).ready(function ($) {
    ('use strict');

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function compactJoin(values, separator) {
        return values
            .filter(function (value) {
                return (
                    value !== undefined &&
                    value !== null &&
                    String(value).trim() !== ''
                );
            })
            .join(separator);
    }

    function buildTravellerAction(traveller) {
        var buyUrl = `${base_url}buy-bag-space/${traveller.hash}`;

        if (
            traveller.destination === 'United Kingdom' ||
            traveller.destination === 'Canada'
        ) {
            if (traveller.is_verified === 0 || traveller.is_verified === 1) {
                return {
                    type: 'modal',
                    html: `<button class="btn btn-primary traveller-card-action" data-bs-toggle="modal" data-bs-target="#verifyID">
                  Buy Space <i class="ti ti-arrow-up-right-circle"></i>
                </button>`,
                };
            }

            if (traveller.bag_locked == 1) {
                return {
                    type: 'disabled',
                    html: `<button class="btn btn-warning traveller-card-action" disabled>
                  Bag is Locked <i class="ti ti-lock"></i>
                </button>`,
                };
            }

            if (traveller.available_space == 0) {
                return {
                    type: 'disabled',
                    html: `<button class="btn btn-danger traveller-card-action" disabled>
                  Bag is Full <i class="ti ti-luggage-off"></i>
                </button>`,
                };
            }

            return {
                type: 'link',
                href: buyUrl,
                html: `<a href="${buyUrl}" class="btn btn-primary traveller-card-action">
                Buy Space <i class="ti ti-luggage"></i>
              </a>`,
            };
        }

        if (traveller.destination === 'Nigeria') {
            if (traveller.profile_completed === 0) {
                return {
                    type: 'modal',
                    html: `<button class="btn btn-primary traveller-card-action" data-bs-toggle="modal" data-bs-target="#goToProfile">
                  Buy Space <i class="ti ti-arrow-up-right-circle fs-5"></i>
                </button>`,
                };
            }

            if (traveller.bag_locked == 1) {
                return {
                    type: 'disabled',
                    html: `<button class="btn btn-warning traveller-card-action" disabled>
                  Bag is Locked <i class="ti ti-lock"></i>
                </button>`,
                };
            }

            if (traveller.available_space == 0) {
                return {
                    type: 'disabled',
                    html: `<button class="btn btn-danger traveller-card-action" disabled>
                  Bag is Full <i class="ti ti-luggage-off"></i>
                </button>`,
                };
            }

            return {
                type: 'link',
                href: buyUrl,
                html: `<a href="${buyUrl}" class="btn btn-primary traveller-card-action">
                Buy Space <i class="ti ti-luggage"></i>
              </a>`,
            };
        }

        if (traveller.bag_locked == 1) {
            return {
                type: 'disabled',
                html: `<button class="btn btn-warning traveller-card-action" disabled>
                Bag is Locked <i class="ti ti-lock"></i>
              </button>`,
            };
        }

        return {
            type: 'link',
            href: buyUrl,
            html: `<a href="${buyUrl}" class="btn btn-primary traveller-card-action">
              Buy Spaces <i class="ti ti-luggage"></i>
            </a>`,
        };
    }

    function buildTravellerCard(traveller) {
        var action = buildTravellerAction(traveller);
        var finalDestination = traveller.destination_area
            ? compactJoin(
                  [traveller.arrival_state, traveller.destination_area],
                  ', '
              )
            : traveller.arrival_state;
        var currentLocation = compactJoin(
            [traveller.area, traveller.current_state],
            ', '
        );
        var routeText = `${currentLocation || 'Current location'} to ${finalDestination || 'Final destination'}`;
        var routeInitial = traveller.destination
            ? String(traveller.destination).charAt(0).toUpperCase()
            : 'S';
        var safeHref = action.href ? escapeHtml(action.href) : '';
        var clickableAttrs =
            action.type === 'link'
                ? ` data-traveller-href="${escapeHtml(action.href)}" tabindex="0" role="link" aria-label="Buy space from traveller traveling on ${escapeHtml(traveller.travel_date)}"`
                : '';
        var clickableClass =
            action.type === 'link' ? ' traveller-result-card-clickable' : '';

        return `
      <article class="traveller-result-card${clickableClass}"${clickableAttrs}>
        <div class="traveller-card-visual" aria-hidden="true">
            <span>${escapeHtml(traveller.available_space)}</span>
            <small>KG available</small>
        </div>

        <div class="traveller-card-content">
          <h5 class="traveller-card-route">${escapeHtml(routeText)}</h5>

          <div class="traveller-card-meta">
            <span><i class="ti ti-calendar"></i> ${escapeHtml(traveller.travel_date)}</span>
            <span><i class="ti ti-clock"></i> ${escapeHtml(traveller.days_remaining)}</span>
            <span><i class="ti ti-plane-departure"></i> ${escapeHtml(traveller.departure_state)}</span>
            <span><i class="ti ti-plane-arrival"></i> ${escapeHtml(traveller.arrival_airport)}</span>
          </div>
        </div>

        <div class="traveller-card-cta">
            <div class="traveller-card-space d-md-none d-lg-none">
                <span>${escapeHtml(traveller.available_space)}</span>
                <small>KG available</small>
            </div>
            ${action.type === 'link' ? action.html.replace(action.href, safeHref) : action.html}
        </div>
      </article>`;
    }

    $('#search-results').on(
        'click',
        '.traveller-result-card-clickable',
        function (e) {
            if ($(e.target).closest('a, button').length) {
                return;
            }

            window.location.href = $(this).data('traveller-href');
        }
    );

    $('#search-results').on(
        'keydown',
        '.traveller-result-card-clickable',
        function (e) {
            if (e.key !== 'Enter' && e.key !== ' ') {
                return;
            }

            e.preventDefault();
            window.location.href = $(this).data('traveller-href');
        }
    );

    // Search
    $('#search_form').submit(function (e) {
        e.preventDefault();
        $('#search-spinner').removeClass('d-none');
        $('#search-results').html('');
        let val = $('#select_destination').val();
        let url = $(this).attr('action');

        if (val.trim() == '') {
            $('#search-spinner').addClass('d-none');
            return;
        }

        let form_data = $(this).serialize();
        $.ajax({
            url: url,
            type: 'POST',
            data: form_data,
            contentType: 'application/x-www-form-urlencoded',
            success: function (response) {
                response = JSON.parse(response);
                let html_response = '';

                if (response.status) {
                    html_response += `
            <div class="card !tw-bg-[#020713] mb-3">
              <div class="card-body">
                <p class="text-white text-center mt-0 mb-0">Last drop off date is 24hrs before a traveller’s departure</p>
              </div>
            </div>

            <div class="traveller-results-header">
              <p>We found <span class="traveller-results-kicker">${response.travellers.length}</span> travellers</p>
            </div>

            <div class="traveller-results-grid">`;

                    response.travellers.forEach(function (traveller) {
                        html_response += buildTravellerCard(traveller);
                    });

                    html_response += `</div>`;

                    setTimeout(function () {
                        $('#search-results').html(html_response);
                        $('#search-spinner').addClass('d-none');
                    }, 2000);
                } else {
                    var noResultsHtml = `
                              <div class="card mb-0">
                                <div class="card-body">
                                    <div class="d-flex justify-content-center">
                                        <dotlottie-wc src="https://lottie.host/764ac1e3-50ac-465b-89a9-8259af46e54b/Qx6tMx1p9G.lottie" style="width: 150px;height: 150px" autoplay loop></dotlottie-wc>
                                    </div>
                                    <h4 class="card-title mb-3 mt-3 text-center">No Traveller Available</h4>
                                </div>
                            </div>`;
                    setTimeout(function () {
                        $('#search-results').html(noResultsHtml);
                        $('#search-spinner').addClass('d-none');
                    }, 2000);
                }
            },
            error: function (error) {
                console.log(`This is the error ${error}`);
                $('#search-spinner').addClass('d-none');
            },
        });
    });
});
