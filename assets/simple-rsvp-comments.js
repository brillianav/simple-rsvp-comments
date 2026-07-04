jQuery(function ($) {
    $('.src-rsvp').each(function () {
        var wrapper = $(this);
        var form = wrapper.find('.src-rsvp__form');
        var list = wrapper.find('.src-rsvp__list');
        var pagination = wrapper.find('.src-rsvp__pagination');
        var alertBox = wrapper.find('.src-rsvp__alert');

        var perPage = parseInt(wrapper.data('per-page'), 10) || 5;
        var currentPage = 1;

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
                        renderPagination(response.data.page, response.data.totalPages);
                    }
                },
                complete: function () {
                    list.removeClass('is-loading');
                }
            });
        }

        function renderPagination(page, totalPages) {
            page = parseInt(page, 10) || 1;
            totalPages = parseInt(totalPages, 10) || 1;

            pagination.empty();

            if (totalPages <= 1) {
                return;
            }

            var prevDisabled = page <= 1 ? ' disabled' : '';
            var nextDisabled = page >= totalPages ? ' disabled' : '';

            pagination.append(
                '<button type="button" class="src-rsvp__page src-rsvp__page--prev"' + prevDisabled + ' data-page="' + (page - 1) + '">Prev</button>'
            );

            for (var i = 1; i <= totalPages; i++) {
                var activeClass = i === page ? ' is-active' : '';

                pagination.append(
                    '<button type="button" class="src-rsvp__page' + activeClass + '" data-page="' + i + '">' + i + '</button>'
                );
            }

            pagination.append(
                '<button type="button" class="src-rsvp__page src-rsvp__page--next"' + nextDisabled + ' data-page="' + (page + 1) + '">Next</button>'
            );
        }

        form.on('submit', function (e) {
            e.preventDefault();

            var button = form.find('.src-rsvp__button');

            button.text('Mengirim...').prop('disabled', true);
            alertBox.removeClass('is-success is-error').text('');

            $.ajax({
                url: SimpleRSVPComments.ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: form.serialize() + '&action=simple_rsvp_submit&nonce=' + SimpleRSVPComments.nonce,
                success: function (response) {
                    if (response.success) {
                        alertBox.addClass('is-success').text(response.data.message);

                        form.find('textarea').val('');
                        form.find('select').val('');

                        loadComments(1);
                    } else {
                        alertBox.addClass('is-error').text(response.data.message);
                    }
                },
                error: function () {
                    alertBox.addClass('is-error').text('Terjadi kesalahan.');
                },
                complete: function () {
                    button.text('Kirim').prop('disabled', false);
                }
            });
        });

        pagination.on('click', '.src-rsvp__page', function () {
            var button = $(this);

            if (button.prop('disabled') || button.hasClass('is-active')) {
                return;
            }

            var page = parseInt(button.data('page'), 10);

            if (!page || page < 1) {
                return;
            }

            loadComments(page);
        });

        loadComments(1);
    });
});