window.addEventListener('submit', function (event) {
    if (!event.target.matches('form.js-event-filter-form')) {
        return;
    }

    event.preventDefault();

    var displayModeField = 'tx_sfeventmgt_pieventlist[overwriteDemand][displayMode]';
    var monthField = 'tx_sfeventmgt_pieventlist[overwriteDemand][timeRestrictionLow]';
    var parameters = [];

    Array.prototype.forEach.call(event.target.elements, function (field) {
        if (!field.name || field.disabled || ((field.type === 'checkbox' || field.type === 'radio') && !field.checked)) {
            return;
        }

        var emptyValue = field.getAttribute('data-empty-value') || '';

        if (field.hasAttribute('data-submit-if-filled') && String(field.value).trim() === emptyValue) {
            return;
        }

        parameters.push({name: field.name, value: String(field.value)});
    });

    var displayMode = parameters.find(function (parameter) {
        return parameter.name === displayModeField;
    });
    var month = parameters.find(function (parameter) {
        return parameter.name === monthField;
    });
    var monthMatch = month
        ? month.value.match(/^((?:19|20|21)[0-9]{2}-(?:0[1-9]|1[0-2]))-01 00:00:00$/)
        : null;
    var url = new URL(event.target.action, window.location.href);

    if (displayMode && displayMode.value === 'time_restriction' && monthMatch) {
        parameters = parameters.filter(function (parameter) {
            return parameter.name !== displayModeField && parameter.name !== monthField;
        });
        url.pathname = url.pathname.replace(/\/$/, '') + '/ab/' + monthMatch[1];
    }

    parameters = parameters.map(function (parameter) {
        var aliases = {
            'tx_sfeventmgt_pieventlist[overwriteDemand][category]': 'kategorie',
            'tx_sfeventmgt_pieventlist[workGroup]': 'kreis',
            'tx_sfeventmgt_pieventlist[searchTerm]': 'suche',
            'tx_sfeventmgt_pieventlist[overwriteDemand][topEventRestriction]': 'top',
            'tx_sfeventmgt_pieventlist[onlineEvent]': 'online'
        };

        if (parameter.name === 'tx_sfeventmgt_pieventlist[overwriteDemand][topEventRestriction]') {
            parameter.value = parameter.value === '2' ? '1' : parameter.value;
        }

        parameter.name = aliases[parameter.name] || parameter.name;

        return parameter;
    });

    url.search = parameters.map(function (parameter) {
        return encodeURIComponent(parameter.name) + '=' + encodeURIComponent(parameter.value);
    }).join('&');
    url.hash = 'event-filter';
    window.location.assign(url.toString());
}, true);

window.addEventListener('submit', function (event) {
    if (!event.target.matches('form.js-news-filter-form')) {
        return;
    }

    event.preventDefault();

    var url = new URL(event.target.action, window.location.href);
    var parameters = new URLSearchParams();

    Array.prototype.forEach.call(event.target.elements, function (field) {
        if (!field.name || field.disabled || ((field.type === 'checkbox' || field.type === 'radio') && !field.checked)) {
            return;
        }

        var value = String(field.value).trim();
        var emptyValue = field.getAttribute('data-empty-value');

        if (emptyValue !== null && value === emptyValue) {
            return;
        }

        parameters.append(field.name, value);
    });

    url.search = parameters.toString();
    url.hash = 'news-filter';
    window.location.assign(url.toString());
}, true);

// we have to call the helllicht functions from /hgon-html/site/snippets/foot.php
$('.js-navbar').helllnav();
$('.js-slider').helllslider();
$('.js-lightbox').helllbox();
$('.js-open-mdl').helllmodal();
$('.js-tabs-container').hellltabs({
  tabIndex: 0
});

/* copy to clipboard function for tp-single share function */
function copyToClipboard() {
    /* Get the text field */
    var copyText = document.getElementById("hgon-url");

    /* Select the text field */
    copyText.select();

    /* Copy the text inside the text field */
    document.execCommand("copy");

    /* Alert the copied text */
    jQuery(".clipboard-container").show().delay(5000).fadeOut(400);

    jQuery('.cb-close').bind('click', function(){
        jQuery('.clipboard-container').hide();
    });
}


jQuery(document).ready(function(){

    jQuery(document).on('click', 'a.btn--loadmore.ajax', function (event) {
        event.preventDefault();

        var $link = jQuery(this);
        var url = $link.attr('href');

        if (!url) {
            return;
        }

        $link.addClass('is-loading');

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Ajax request failed with status ' + response.status);
                }
                return response.json();
            })
            .then(function (payload) {
                if (!payload || !payload.html) {
                    throw new Error('Invalid ajax payload');
                }

                Object.keys(payload.html).forEach(function (targetId) {
                    var updates = payload.html[targetId];
                    var target = document.getElementById(targetId);

                    if (!target) {
                        return;
                    }

                    if (typeof updates.replace === 'string') {
                        target.innerHTML = updates.replace;
                    }

                    if (typeof updates.append === 'string') {
                        target.insertAdjacentHTML('beforeend', updates.append);
                    }
                });
            })
            .catch(function (error) {
                console.error(error);
            })
            .finally(function () {
                $link.removeClass('is-loading');
            });
    });

    jQuery(document).on('click', 'a.ajax-html-loadmore', function (event) {
        event.preventDefault();

        var $link = jQuery(this);
        var url = $link.attr('href');
        var appendTargetId = $link.attr('data-append-target');
        var paginationTargetId = $link.attr('data-pagination-target');
        var pageRootId = $link.attr('data-page-root');

        if (!url || !appendTargetId || !paginationTargetId || !pageRootId) {
            return;
        }

        var appendTarget = document.getElementById(appendTargetId);
        var paginationTarget = document.getElementById(paginationTargetId);

        if (!appendTarget || !paginationTarget) {
            return;
        }

        $link.addClass('is-loading');

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('HTML load-more request failed with status ' + response.status);
                }
                return response.text();
            })
            .then(function (html) {
                var parser = new DOMParser();
                var doc = parser.parseFromString(html, 'text/html');
                var nextAppendTarget = doc.getElementById(appendTargetId);
                var nextPaginationTarget = doc.getElementById(pageRootId);

                if (!nextAppendTarget) {
                    throw new Error('Append target not found in response');
                }

                appendTarget.insertAdjacentHTML('beforeend', nextAppendTarget.innerHTML);
                paginationTarget.innerHTML = nextPaginationTarget ? nextPaginationTarget.innerHTML : '';
            })
            .catch(function (error) {
                console.error(error);
            })
            .finally(function () {
                $link.removeClass('is-loading');
            });
    });

    // hide events more button after click. If there are further entries, a new button is created by the ajax loaded template
    jQuery(document).on('click', 'a.next-page.autoload', function (event) {
       jQuery(this).parent('div.align-center').parent('div.grid').remove();
    });


    jQuery('.link--topanchor').click(function(e) {
        $('body,html').animate({
           scrollTop: 0
       }, 800);
        e.preventDefault();
    });

});
