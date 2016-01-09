$(document).ready(function() {

    /**
     *
     * RESPONDING TO TICKET
     *
     */
    $('#respond-main').on('click', '#respond-btn', function() {
        var tickets = [];

        $('.ticket-panel').each(function() {
            // Create ticket object
            tickets.push({
                "id": $(this).find('.ticket-id').val(),
                "test_status": $(this).find('input[type="radio"]:checked').val(),
                "notes_response": $(this).find('.notes-response').val()
            });
        });

        // Create hidden field
        var input = $("<input>")
            .attr("type", "hidden")
            .attr("name", "tickets_obj").val(JSON.stringify(tickets));

        $('form').append($(input));
    });


    /**
     *
     * VIEW RESPONSE DROPDOWN VIEWER FOR A CERTAIN USER
     *
     */
    $('#view-response-main').on('change', '#view-tester', function() {
        var route  = $(this).data('url');
        var userId = $(this).val();
        var planId = $('#plan_id').val();

        if (userId != '') {
            window.location.href = route + '/' + planId + "/" + userId;
        }
    });


    /**
     *
     * DASHBOARD
     *
     */
    $('#dashboard-main .admin_created_plans_rows').each(function() {
        var testerId = $(this).find('.testers option:nth-child(1)').val();
        var route = $(this).find('.testers').data('url') + '/' + testerId;
        var link = $(this).find('.plan-link').prop('href', route);
    });

    // Change viewer id link
    $('#dashboard-main').on('change', '.testers', function() {
        var selectedTesterId = $(this).val();
        var route = $(this).data('url') + '/' + selectedTesterId;

        $(this).closest('td').next('td').find('.plan-link').prop('href', route);
    });

    // Hide initially
    $('.activity-comment-content').hide();

    // Toggle comment to show or hide
    $('#dashboard-main').on('click', '.activity-comment-link', function(e) {
        e.preventDefault();
        var parent = $(this).parentsUntil('.activity-log');

        parent.find('.activity-comment-content').toggle();
    });

    // Add comment
    $('#dashboard-main').on('click', '.activity-comment-add', function() {
        var parent  = $(this).parentsUntil('.activity-log');
        var logId   = parent.find('.log_id').val();
        var comment = parent.find('.activity-comment').val();

        $.ajax({
            method: "POST",
            url: "{!! URL::to('dashboard/save-comment') !!}",
            data: {
                "_token":  $('form').find('input[name=_token]').val(),
                "id":      logId,
                "comment": comment
            },
            dataType: "json"
        }).done(function(msg) {
            location.reload();
        });
    });

    // Cancel comment
    $('#dashboard-main').on('click', '.activity-comment-cancel', function() {
        var parent = $(this).parentsUntil('.activity-log');
        parent.find('.activity-comment-content').hide();
    });


    /**
     *
     * VIEW ALL ADMIN PLANS
     *
     */
    // View all admin
    $('#view-all-admin-main').on('click', '.view-tester-plan', function(e) {
        e.preventDefault();

        var parent = $(this).closest('tr');
        var tester = parent.find('.tester').val();
        var url    = $(this).attr('href');

        window.location.href = url + '/' + tester;
    });

    $('#view-all-assigned-main').on('click', '.toggler', function() {
        window.location.href = $(this).data('url');
    });


    /**
     *
     * USER ACCOUNTS
     *
     */
    // Display all user accounts
    $('#view-all-users-main').on('click', '.toggler', function(e) {
        e.preventDefault();

        var currentClass = $('#view-all-users-main').attr('class');

        if (currentClass != 'col-xs-12 col-md-8') {
            // Control width of both columns
            $('#view-all-users-main').toggleClass('col-md-12 col-md-8');
            $('#viewer-main').toggleClass('col-md-0 col-md-4');
        }

        // Selecting rows on mobile
        if (currentClass == 'col-xs-12 col-md-12') {
            $('#view-all-users-main').css({'z-index': '1000'});
        }

        $.when(
            $.ajax({
                method: "GET",
                url: $(this).data('url'),
                dataType: "json",
                success: function (resp) {
                    $('#viewer-main').html(resp.viewBody);
                }
            })
        ).done(function (resp) {
            // Close viewer
            $('.close-viewer').on('click', function (e) {
                e.preventDefault();
                $('#view-all-users-main').toggleClass('col-md-12 col-md-8');
                $('#viewer-main').toggleClass('col-md-0 col-md-4');
                $('#viewer-main').empty();
            });
        });
    });
});