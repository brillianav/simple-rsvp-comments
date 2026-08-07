jQuery(function ($) {
    $('.src-rsvp').each(function () {
        var wrapper = $(this);
        var form = wrapper.find('.src-rsvp__form');
        var list = wrapper.find('.src-rsvp__list');
        var pagination = wrapper.find('.src-rsvp__pagination');
        var alertBox = wrapper.find('.src-rsvp__alert');

        var perPage = parseInt(wrapper.data('per-page'), 10) || 5;
        var currentPage = 1;

        /* =========================================
           STATUS BADGE
        ========================================= */

        function normalizeStatus(value) {
            return String(value || '')
                .toLowerCase()
                .trim()
                .replace(/\s+/g, ' ');
        }

        function applyStatusBadgeColors() {
            list.find('.src-rsvp__badge').each(function () {
                var badge = $(this);
                var status = normalizeStatus(badge.text());

                badge
                    .removeClass(
                        'is-hadir is-ragu is-tidak-hadir'
                    )
                    .removeAttr('data-status');

                /* Hadir */
                if (
                    status === 'hadir' ||
                    status === 'attending' ||
                    status === 'attend'
                ) {
                    badge
                        .addClass('is-hadir')
                        .attr('data-status', 'hadir');

                    return;
                }

                /* Masih ragu */
                if (
                    status === 'masih ragu' ||
                    status === 'ragu' ||
                    status === 'maybe' ||
                    status === 'tentative'
                ) {
                    badge
                        .addClass('is-ragu')
                        .attr('data-status', 'masih-ragu');

                    return;
                }

                /* Tidak hadir */
                if (
                    status === 'tidak hadir' ||
                    status === 'tidak bisa hadir' ||
                    status === 'berhalangan hadir' ||
                    status === 'not attending' ||
                    status === 'absent'
                ) {
                    badge
                        .addClass('is-tidak-hadir')
                        .attr('data-status', 'tidak-hadir');
                }
            });
        }

        /* =========================================
           LOAD COMMENTS
        ========================================= */

        function loadComments(page) {
            currentPage = page || 1;

            list.addClass('is-loading');

            $.ajax({
                url: SimpleRSVPComments.ajaxUrl,
                type: 'POST',
                dataType: 'json',

                data: {
                    action: 'simple_rsvp_load',
                    nonce: SimpleRSVPComments.nonce,
                    page: currentPage,
                    per_page: perPage
                },

                success: function (response) {
                    if (response.success) {
                        list.html(response.data.html);

                        /*
                         * Terapkan warna badge setelah
                         * komentar AJAX dimasukkan.
                         */
                        applyStatusBadgeColors();

                        renderPagination(
                            response.data.page,
                            response.data.totalPages
                        );
                    }
                },

                error: function () {
                    list.html(
                        '<div class="src-rsvp__empty">' +
                        'Gagal memuat komentar.' +
                        '</div>'
                    );

                    pagination.empty();
                },

                complete: function () {
                    list.removeClass('is-loading');
                }
            });
        }

        /* =========================================
           PAGINATION
        ========================================= */

        function renderPagination(page, totalPages) {
            page = parseInt(page, 10) || 1;
            totalPages = parseInt(totalPages, 10) || 1;

            pagination.empty();

            if (totalPages <= 1) {
                return;
            }

            var prevDisabled = page <= 1
                ? ' disabled'
                : '';

            var nextDisabled = page >= totalPages
                ? ' disabled'
                : '';

            pagination.append(
                '<button ' +
                    'type="button" ' +
                    'class="src-rsvp__page src-rsvp__page--prev"' +
                    prevDisabled +
                    ' data-page="' + (page - 1) + '">' +
                    'Prev' +
                '</button>'
            );

            var visiblePages = getVisiblePaginationPages(
                page,
                totalPages
            );

            visiblePages.forEach(function (item) {
                if (item === 'ellipsis') {
                    pagination.append(
                        '<span ' +
                            'class="src-rsvp__ellipsis" ' +
                            'aria-hidden="true">' +
                            '...' +
                        '</span>'
                    );

                    return;
                }

                var activeClass = item === page
                    ? ' is-active'
                    : '';

                pagination.append(
                    '<button ' +
                        'type="button" ' +
                        'class="src-rsvp__page' + activeClass + '" ' +
                        'data-page="' + item + '">' +
                        item +
                    '</button>'
                );
            });

            pagination.append(
                '<button ' +
                    'type="button" ' +
                    'class="src-rsvp__page src-rsvp__page--next"' +
                    nextDisabled +
                    ' data-page="' + (page + 1) + '">' +
                    'Next' +
                '</button>'
            );
        }

        function getVisiblePaginationPages(page, totalPages) {
            if (totalPages <= 4) {
                var allPages = [];

                for (var i = 1; i <= totalPages; i++) {
                    allPages.push(i);
                }

                return allPages;
            }

            if (page <= 2) {
                return [1, 2, 'ellipsis', totalPages];
            }

            if (page >= totalPages - 1) {
                return [1, 'ellipsis', totalPages - 1, totalPages];
            }

            return [1, 'ellipsis', page, 'ellipsis', totalPages];
        }

        /* =========================================
           SUBMIT FORM
        ========================================= */

        form.on('submit', function (e) {
            e.preventDefault();

            var button = form.find('.src-rsvp__button');
            var originalButtonText = button.text();

            button
                .text('Mengirim...')
                .prop('disabled', true);

            alertBox
                .removeClass('is-success is-error')
                .text('');

            $.ajax({
                url: SimpleRSVPComments.ajaxUrl,
                type: 'POST',
                dataType: 'json',

                data:
                    form.serialize() +
                    '&action=simple_rsvp_submit' +
                    '&nonce=' +
                    encodeURIComponent(SimpleRSVPComments.nonce),

                success: function (response) {
                    if (response.success) {
                        alertBox
                            .addClass('is-success')
                            .text(response.data.message);

                        form.find('textarea').val('');
                        form.find('select').val('');

                        loadComments(1);
                    } else {
                        var errorMessage =
                            response.data &&
                            response.data.message
                                ? response.data.message
                                : 'Gagal mengirim RSVP.';

                        alertBox
                            .addClass('is-error')
                            .text(errorMessage);
                    }
                },

                error: function () {
                    alertBox
                        .addClass('is-error')
                        .text('Terjadi kesalahan.');
                },

                complete: function () {
                    button
                        .text(originalButtonText || 'Kirim')
                        .prop('disabled', false);
                }
            });
        });

        /* =========================================
           PAGINATION CLICK
        ========================================= */

        pagination.on(
            'click',
            '.src-rsvp__page',
            function () {
                var button = $(this);

                if (
                    button.prop('disabled') ||
                    button.hasClass('is-active')
                ) {
                    return;
                }

                var page = parseInt(
                    button.data('page'),
                    10
                );

                if (!page || page < 1) {
                    return;
                }

                loadComments(page);
            }
        );

        /* Terapkan jika sudah ada HTML bawaan */
        applyStatusBadgeColors();

        /* Initial load */
        loadComments(1);
    });
});
