// custom-reporting-script.js
jQuery(document).ready(function($) {
    // DataTable initialization
    $('#learndash-courses').DataTable({
       
        ajax: {
            url: ajax_object.ajax_url,
            type: "POST",
            data:function(d) {
            d.action = "get_courese_data";
        }
        },
        'columns': [
            { data: 'ID',"orderable": false },
            { data: 'Course',"orderable": false},
            { data: 'Enrolled' },
            { data: 'MedianGradePercentage' },
            { data: 'MedianAdherenceRate' },
            { data: 'NotStarted' },
            { data: 'InProgress' },
            { data: 'Completed' },
            { data: 'AvgQuizScore' },
            { data: 'PercentComplete' },
            { data: 'Details' },
        ],
        responsive: true,
        // DataTable options and configurations
        "paging": true,
        "order": [],
        "searching": true, // Enable search box
        
        "buttons": [
            {
                extend: 'excel',
                text: 'Excel Export', // Custom title for Excel button
              
            },
            {
                extend: 'csv',
                text: 'CSV Export', // Custom title for CSV button
               
            },// Enable export buttons
        ],
        "pageLength": 10,  // Number of records to show per page
        "lengthMenu": [10, 25, 50, 100], 
        "dom": '<"top"f>rt<"bottom"lp><"clear">',
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search..."
        },
        // Add more options and configurations as needed
    });
    
    var urlParams = new URLSearchParams(window.location.search);
    var course_id = urlParams.get('course_id');

    var table = $('#learndash-course-users').DataTable({
        "processing": true,
        "serverSide": true,
        "responsive": true,
        // DataTable options and configurations
        "paging": true,
        "ordering": true,
        "searching": true, // Enable search box
        "dom": 'Bfrtip',
        "buttons": [
            {
                extend: 'excel',
                text: 'Excel Export', // Custom title for Excel button
              
            },
            {
                extend: 'csv',
                text: 'CSV Export', // Custom title for CSV button
               
            },
        ], 
        "ajax": {
            "url": ajax_object.ajax_url,
            "type": "POST",
            "data": {
                'action': 'course_users_ajax',
                'course_id': course_id
            }
        },
        "columns": [
            { "data": 'userName' }, // User Name
            { "data": 'userEmail' }, // User Email
            { "data": 'quizAverage' }, // Quiz Average
            { "data": 'completionDate' }, // Completion Date
            { "data": 'completedLessons' }, // Completed Lessons
            { "data": 'classType' }, // Cohort ID
            { "data": 'cohortId' }, // Class Type
            { "data": 'cohortName' }, // Cohort Name
            { "data": 'cohortValue' },
            { "data": 'status', render: function(data, type, row) {
                // Render a dropdown for the 'role' column
                console.log('Row',row);
                if (type === 'display' || type === 'filter') {
                    // The 'selected' attribute will be set for the correct option
                    return '<select class="status-dropdown" data-user-id="' + row.userEmail + '">' +
                        '<option value="">Select Status</option>' +
                        '<option value="Ahead" ' + (data === 'Ahead' ? 'selected' : '') + '>Ahead</option>' +
                        '<option value="Behind" ' + (data === 'Behind' ? 'selected' : '') + '>Behind</option>' +
                        '<option value="Excelling" ' + (data === 'Excelling' ? 'selected' : '') + '>Excelling</option>' +
                        '</select>';
                }
                return data;
            } }, // Cohort Value
            { "data": 'timer' }, // Timer
            { "data": 'topics' }, // Topics
            { "data": 'startDate' }, // Start Date
            { "data": 'endDate' }, // End Date
            { "data": 'userTags' }, // End Date
            { "data": 'timeElapsed' }, // Time Elapsed
            { "data": 'adherenceRate' }, // Adherence Rate
            { "data": 'quiz' }, // Quiz
            { "data": 'lastLogin' }, // Last Login
            { "data": 'percentComplete' }, // % Complete
            // { "data": 'details' }  // Details // Enable export buttons
    ],
        // Add more options and configurations as needed
    });

       // Custom range filter function
   /* $.fn.dataTable.ext.search.push(
        function (settings, data, dataIndex) {
            console.log("Data:", data);
            var min = $('#min-date').val();
            var max = $('#max-date').val();
            var startDate = new Date(data.startDate); // Assuming the key for start date is 'startDate'
            var endDate = new Date(data.endDate); // Assuming the key for end date is 'endDate'

            if ((min === "" && max === "") ||
                (min === "" && endDate.getTime() <= new Date(max).getTime()) ||
                (startDate.getTime() >= new Date(min).getTime() && max === "") ||
                (startDate.getTime() >= new Date(min).getTime() && endDate.getTime() <= new Date(max).getTime())) {
                return true;
            }
            return false;
        }
    );*/

    // Re-draw the table when the a date range filter changes
    $('.date-range-filter').change(function() {
        $("#learndash-course-users").dataTable().fnDestroy();

        var urlParams = new URLSearchParams(window.location.search);
        var course_id = urlParams.get('course_id');

        var min = $('#min-date').val();
        var max = $('#max-date').val();

        var table = $('#learndash-course-users').DataTable({
                "processing": true,
                "serverSide": true,
                "responsive": true,
                // DataTable options and configurations
                "paging": true,
                "ordering": true,
                "searching": true, // Enable search box
                "dom": 'Bfrtip',
                "buttons": [
                    {
                        extend: 'excel',
                        text: 'Excel Export', // Custom title for Excel button
                      
                    },
                    {
                        extend: 'csv',
                        text: 'CSV Export', // Custom title for CSV button
                       
                    },
                ], 
                "ajax": {
                    "url": ajax_object.ajax_url,
                    "type": "POST",
                    "data": {
                        'action': 'course_users_ajax',
                        'course_id': course_id,
                        'min_date': min,
                        'max_date': max,

                    }
                },
                "columns": [
                    { "data": 'userName' }, // User Name
                    { "data": 'userEmail' }, // User Email
                    { "data": 'quizAverage' }, // Quiz Average
                    { "data": 'completionDate' }, // Completion Date
                    { "data": 'completedLessons' }, // Completed Lessons
                    { "data": 'classType' }, // Cohort ID
                    { "data": 'cohortId' }, // Class Type
                    { "data": 'cohortName' }, // Cohort Name
                    { "data": 'cohortValue' },
                    { "data": 'status', render: function(data, type, row) {
                        // Render a dropdown for the 'role' column
                        console.log('Row',row);
                        if (type === 'display' || type === 'filter') {
                            // The 'selected' attribute will be set for the correct option
                            return '<select class="status-dropdown" data-user-id="' + row.userEmail + '">' +
                                '<option value="">Select Status</option>' +
                                '<option value="Ahead" ' + (data === 'Ahead' ? 'selected' : '') + '>Ahead</option>' +
                                '<option value="Behind" ' + (data === 'Behind' ? 'selected' : '') + '>Behind</option>' +
                                '<option value="Excelling" ' + (data === 'Excelling' ? 'selected' : '') + '>Excelling</option>' +
                                '</select>';
                        }
                        return data;
                    } }, // Cohort Value
                    
                    { "data": 'timer' }, // Timer
                    { "data": 'topics' }, // Topics
                    { "data": 'startDate' }, // Start Date
                    { "data": 'endDate' }, // End Date
                    { "data": 'userTags' }, // Timer, // Cohort Value
                    { "data": 'timeElapsed' }, // Time Elapsed
                    { "data": 'adherenceRate' }, // Adherence Rate
                    { "data": 'quiz' }, // Quiz
                    { "data": 'lastLogin' }, // Last Login
                    { "data": 'percentComplete' }, // % Complete
                    // { "data": 'details' }  // Details // Enable export buttons
            ],
                // Add more options and configurations as needed
            });

    });


    $('#filterUsers').on('change', function() {
            $("#learndash-course-users").dataTable().fnDestroy();

        var urlParams = new URLSearchParams(window.location.search);
        var course_id = urlParams.get('course_id');

        var selectedTags = $('#filterUsers').val();

        var table = $('#learndash-course-users').DataTable({
                "processing": true,
                "serverSide": true,
                "responsive": true,
                // DataTable options and configurations
                "paging": true,
                "ordering": true,
                "searching": true, // Enable search box
                "dom": 'Bfrtip',
                "buttons": [
                    {
                        extend: 'excel',
                        text: 'Excel Export', // Custom title for Excel button
                      
                    },
                    {
                        extend: 'csv',
                        text: 'CSV Export', // Custom title for CSV button
                       
                    },
                ], 
                "ajax": {
                    "url": ajax_object.ajax_url,
                    "type": "POST",
                    "data": {
                        'action': 'course_users_ajax',
                        'course_id': course_id,
                        'selectedTags' : selectedTags

                    }
                },
                "columns": [
                    { "data": 'userName' }, // User Name
                    { "data": 'userEmail' }, // User Email
                    { "data": 'quizAverage' }, // Quiz Average
                    { "data": 'completionDate' }, // Completion Date
                    { "data": 'completedLessons' }, // Completed Lessons
                    { "data": 'classType' }, // Cohort ID
                    { "data": 'cohortId' }, // Class Type
                    { "data": 'cohortName' }, // Cohort Name
                    { "data": 'cohortValue' },
                    { "data": 'status', render: function(data, type, row) {
                        // Render a dropdown for the 'role' column
                        console.log('Row',row);
                        if (type === 'display' || type === 'filter') {
                            // The 'selected' attribute will be set for the correct option
                            return '<select class="status-dropdown" data-user-id="' + row.userEmail + '">' +
                                '<option value="">Select Status</option>' +
                                '<option value="Ahead" ' + (data === 'Ahead' ? 'selected' : '') + '>Ahead</option>' +
                                '<option value="Behind" ' + (data === 'Behind' ? 'selected' : '') + '>Behind</option>' +
                                '<option value="Excelling" ' + (data === 'Excelling' ? 'selected' : '') + '>Excelling</option>' +
                                '</select>';
                        }
                        return data;
                    } }, // Cohort Value
                    
                    { "data": 'timer' }, // Timer
                    { "data": 'topics' }, // Topics
                    { "data": 'startDate' }, // Start Date
                    { "data": 'endDate' }, // End Date
                    { "data": 'userTags' }, // Timer, // Cohort Value
                    { "data": 'timeElapsed' }, // Time Elapsed
                    { "data": 'adherenceRate' }, // Adherence Rate
                    { "data": 'quiz' }, // Quiz
                    { "data": 'lastLogin' }, // Last Login
                    { "data": 'percentComplete' }, // % Complete
                    // { "data": 'details' }  // Details // Enable export buttons
            ],
                // Add more options and configurations as needed
            }); 
    });
    // Add an event listener for dropdown change
    $('#learndash-course-users').on('change', '.status-dropdown', function() {
        var userEmail = $(this).data('user-id');
        var selectedStatus = $(this).val();

        // Make the AJAX request
        $.ajax({
            url: ajax_object.ajax_url, // WordPress AJAX URL
            type: 'POST',
            data: {
                action: 'update_user_role', // WordPress action hook
                email: userEmail,
                status: selectedStatus
            },
            success: function(response) {
                // Handle the response if needed
                console.log(response);
            },
            error: function(error) {
                // Handle the error if needed
                console.error(error);
            }
        });
    });

});

jQuery(document).ready(function($) {
  $(".chosen-select").chosen({
    no_results_text: "Oops, nothing found!"
  });
});