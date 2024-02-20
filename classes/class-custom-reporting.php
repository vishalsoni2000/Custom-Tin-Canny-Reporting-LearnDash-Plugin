<?php class Custom_Reporting
{

    public function __construct()
    {
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));

        // Enqueue scripts
        add_action('admin_enqueue_scripts', array($this, 'enqueue_data_table_scripts'));
        add_action('admin_menu', array($this, 'add_course_users_admin_page'));
        add_action('wp_ajax_get_courese_data', array($this, 'get_courses_data_ajax_callback'));
        add_action('wp_ajax_nopriv_get_courese_data', array($this, 'get_courses_data_ajax_callback')); // For non-logged-in users
        add_action('wp_ajax_course_users_ajax', array($this,'course_users_ajax_callback'));
        add_action('wp_ajax_nopriv_course_users_ajax', array($this,'course_users_ajax_callback'));
        add_action('wp_ajax_update_user_role', array($this,'update_user_role_callback'));
        add_action('wp_ajax_nopriv_update_user_role', array($this,'update_user_role_callback'));
    }

    public function add_admin_menu()
    {
        add_menu_page(
            'Custom Reporting',
            'Custom Reporting',
            'manage_options',
            'custom_reporting',
            array($this, 'custom_reporting_page')
        );
    }

    public function add_course_users_admin_page()
    {
        add_submenu_page(
            'custom_reporting', // Replace with the parent menu slug of your custom_reporting_page
            'Course Users',
            'Course Users',
            'manage_options',
            'course_users',
            array($this, 'course_users_admin_page_content')
        );
    }

    public function enqueue_data_table_scripts($hook)
    {
        // echo $hook;
        wp_enqueue_style('dataTables', 'https://cdn.datatables.net/v/dt/dt-1.13.8/r-2.5.0/datatables.min.css', array(), '1.10.25');
        wp_enqueue_style('choosen', 'https://cdn.rawgit.com/harvesthq/chosen/gh-pages/chosen.min.css', array(), 'x.x.x');

        wp_enqueue_style('ct-datatable-css', plugin_dir_url(__FILE__) . '../css/datatables.min.css', array(), '1.10.25');

        if (($hook == 'toplevel_page_custom_reporting') || ($hook == 'custom-reporting_page_course_users')) {
            wp_enqueue_script('jquery');
            wp_enqueue_script('choosen', ' https://cdn.rawgit.com/harvesthq/chosen/gh-pages/chosen.jquery.min.js', array('jquery'), 'x.x.x', true);
            //wp_enqueue_script('dataTables', 'https://cdn.datatables.net/v/dt/dt-1.13.8/r-2.5.0/datatables.min.js', array('jquery'), '1.10.25', true);
            wp_enqueue_script('ct-dataTables', plugin_dir_url(__FILE__) . '../js/jquery.dataTables.min.js', array('jquery'), '1.0', true);

            // Enqueue scripts for DataTables Buttons
            wp_enqueue_script('data-table-html-buttons-js', 'https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js', array('jquery'), 'x.x.x', true);


            wp_enqueue_script('data-table-html-buttons', 'https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js', array('jquery'), 'x.x.x', true);
            wp_enqueue_script('data-table-jszip', 'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js', array('jquery'), 'x.x.x', true);

            wp_enqueue_script('data-table-pdf-maker', 'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js', array('jquery'), 'x.x.x', true);

            wp_enqueue_script('data-table-vfs_fonts', 'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js', array('jquery'), 'x.x.x', true);

            wp_enqueue_script('data-table-print-js', 'https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js', array('jquery'), 'x.x.x', true);

            wp_enqueue_script('reporting-script', plugin_dir_url(__FILE__) . '../js/reporting.js', array('jquery'), rand(), true);
            // Localize the script with new data
            wp_localize_script('reporting-script', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
        }
    }

    public function custom_reporting_page()
    {
?>
        <div class="wrap">
            <h2>LearnDash Courses Report</h2>
            <table id="learndash-courses" class="wp-list-table widefat fixed striped table-view-list users">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Course</th>
                        <th>Enrolled</th>
                        <th>Median Grade Percentage</th>
                        <th>Median Adherence Rate</th>
                        <th>Not Started</th>
                        <th>In Progress</th>
                        <th>Completed</th>
                        <th>Avg Quiz Score</th>
                        <th>% Complete</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    <?php
    }

    public function course_users_admin_page_content()
    {
        if(!isset($_GET['course_id'])){
            wp_redirect(admin_url('admin.php?page=custom_reporting'));
            exit;
        }
    ?>
        <div class="wrap">
            <h2>Course Users</h2>
            <div class="container">
                <div class="col-md-4 pull-right">
                    <div class="input-group input-daterange">

                    <input type="date" id="min-date" class="form-control date-range-filter" data-date-format="yyyy-mm-dd" placeholder="Enrolled date:">
                    <input type="date" id="max-date" class="form-control date-range-filter" data-date-format="yyyy-mm-dd" placeholder="Eneded date:">

                    </div>
                <div class="col-md-4 pull-right">
                <?php
                    // Array to store all user tags
                    $user_tags = array();

                    // Get all user IDs
                    $user_ids = get_users(array('fields' => 'ID'));

                    // Loop through each user
                    foreach ($user_ids as $user_id) {
                        // Check if ACF rows exist for user tags
                        if (have_rows('user_tags', 'user_' . $user_id)) {
                            // Loop through ACF rows
                            while (have_rows('user_tags', 'user_' . $user_id)) {
                                the_row();
                                // Get user tag value and add it to the $user_tags array
                                $user_tags[] = get_sub_field('select_user_tags');
                            }
                        }
                    }

                    // Remove duplicate tags
                    $user_tags = array_unique($user_tags);

                    // Output the multi-select dropdown
                    ?>
                    <select id="filterUsers" data-placeholder="Tags filter..." multiple class="chosen-select" name="test">
                        <option value=""></option>
                        <?php
                        // Loop through user tags and populate dropdown options
                        foreach ($user_tags as $tag) {
                            ?>
                            <option value="<?php echo $tag; ?>"><?php echo $tag; ?></option>
                            <?php
                        }
                        ?>
                    </select>
                </div>
            </div>

        </div>
            <!-- DataTable for course users -->
            <table id="learndash-course-users" class="wp-list-table widefat fixed striped table-view-list users">
                <thead>
                        <th>Name</th>
                        <th>Email Address</th>
                        <th>Quiz Average</th>
                        <th>Completion Date</th>
                        <th>Completed Lessons</th>
                        <th>Class Type</th>
                        <th>Cohort ID</th>
                        <th>Cohort Name</th>
                        <th>Cohort Value</th>
                        <th>Status</th>
                        <th>Timer</th>
                        <th>Topics</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Tags</th>
                        <th>Time Elapsed</th>
                        <th>Adherence Rate</th>
                        <th>Quiz</th>
                        <th>Last Login</th>
                        <th>% Complete</th>
                        <!-- <th>Details</th> -->
                    </tr>

                </thead>
                <tbody>

                    <!-- < ?php
                    // Check if the course ID is provided in the URL
                    $course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

                    if ($course_id) {
                        // Fetch course users based on the course ID (customize this part based on your LearnDash setup)
                        $course_users_query = learndash_get_users_for_course($course_id, array(), false);
                        if ($course_users_query) {
                            $user_ids               = $course_users_query->get_results();
                            if (!empty($user_ids)) {
                                foreach ($user_ids as $userid) {
                                    $user = get_user_by('id', $userid);

                                    $user_id = $user->ID;
                                    $user_name = $user->display_name;
                                    $user_email = $user->user_email;
                                    $total_lession = 0;
                                    $completed_lession = 0;

                                    $total_lession = learndash_get_course_steps_count($course_id);
                                    $completed_lession = learndash_course_get_completed_steps($user_id, $course_id);
                                    $complition_percentage = ($completed_lession * 100) / $total_lession;

                    ?>
                                    <tr>
                                        <td>< ?php echo $user_id; ?></td>
                                        <td>< ?php echo $user_name; ?></td>
                                        <td>< ?php echo $user_email; ?></td>
                                        <td></td>
                                        <td></td>
                                        <td>< ?php echo $completed_lession . '/' . $total_lession; ?></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td>< ?php $complition_percentage . '%' ?></td>
                                        <td></td>

                                    </tr>
                    < ?php
                                }
                            } else {
                                echo '<tr><td colspan="19">No course ID provided.</td></tr>';
                            }
                        } else {
                            echo '<tr><td colspan="19">No User found.</td></tr>';
                        }
                    }
                    ?> -->
                </tbody>
            </table>
        </div>
<?php
    }

    private static function get_course_quiz_average( $course_id, $user_activities, $user_ids ) {

		$quiz_scores = array();

		foreach ( $user_activities as $activity ) {

			if ( isset( $user_ids[ (int) $activity->user_id ] ) && $course_id == $activity->course_id ) {

				if ( ! isset( $quiz_scores[ $activity->post_id . $activity->user_id ] ) ) {

					$quiz_scores[ $activity->post_id . $activity->user_id ] = $activity->activity_percentage;
				} elseif ( $quiz_scores[ $activity->post_id . $activity->user_id ] < $activity->activity_percentage ) {

					$quiz_scores[ $activity->post_id . $activity->user_id ] = $activity->activity_percentage;
				}
			}
		}

		if ( 0 !== count( $quiz_scores ) ) {
			$average = absint( array_sum( $quiz_scores ) / count( $quiz_scores ) );
		} else {
			$average = 'false';
		}

		return $average;
	}


    function get_courses_data_ajax_callback()
    {
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
//error_reporting(E_ALL);
$today = new DateTime();
        $start = isset($_GET['start']) ? intval($_GET['start']) : 0;
        $length = isset($_GET['length']) ? intval($_GET['length']) : 10;

        $args = array(
            'post_type' => 'sfwd-courses',
            'posts_per_page' => $length,
            'offset' => $start,
            'post_status' => 'publish',
        );

        $query = new WP_Query($args);

        $data = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $course_id = get_the_ID();
                $total_users = 0;
                $course_users_query = learndash_get_users_for_course($course_id, array(), false);

                if ($course_users_query) {
                    $user_ids = $course_users_query->get_results();
                    $total_users = count($user_ids);
                    global $wpdb;
                    $meta_key = $wpdb->prefix . 'capabilities';

                    $q_completions = $wpdb->prepare( "
                        SELECT
                            a.post_id as course_id,
                            a.user_id,
                            a.activity_completed
                        FROM
                            {$wpdb->prefix}learndash_user_activity a
                        JOIN
                            {$wpdb->prefix}usermeta um ON a.user_id = um.user_id
                        WHERE
                            a.activity_type = 'course'
                            AND a.course_id = %d
                            AND a.activity_completed IS NOT NULL
                            AND a.activity_completed <> 0
                            AND um.meta_key = %s
                            AND NOT um.meta_value LIKE '%administrator%'
                    ", $course_id, $meta_key );

                    $completed_courses = $wpdb->get_results( $q_completions );
                    $completed_courses_count = count( $completed_courses );


                    $q_progress = $wpdb->prepare( "
                    SELECT
                        a.post_id as course_id,
                        a.user_id,
                        a.activity_completed
                    FROM
                        {$wpdb->prefix}learndash_user_activity a
                    JOIN
                        {$wpdb->prefix}usermeta um ON a.user_id = um.user_id
                    WHERE
                        a.activity_type = 'course'
                        AND a.course_id = %d
                        AND ( a.activity_completed = 0 OR a.activity_completed IS NULL )
						AND ( a.activity_started != 0 OR a.activity_updated != 0 )
                        AND um.meta_key = %s
                        AND NOT um.meta_value LIKE '%administrator%'
                ", $course_id, $meta_key );

                $progress_courses = $wpdb->get_results( $q_progress );
                $progress_courses_count = count( $progress_courses );

		// print_r($wpdb->get_results( $q_completions ));

        $not_started_count = $total_users - ($progress_courses_count+$completed_courses_count);

        $users = learndash_get_users_for_course($course_id);
		$enrolled_users = $users->get_results();
		$all_adherence_rate = [];
		foreach ( $enrolled_users as $user_id ) {

			$start_date = date('Y-m-d', ld_course_access_from($course_id, $user_id));
			$d2 = new DateTime($start_date);
			$interval = $d2->diff($today);
			$time_elapsed = $interval->format('%a'); //day

			$progress = learndash_user_get_course_progress($user_id, $course_id);
			$completion_progress = round((100 * $progress['completed']) / $progress['total']);
			$adherence_rate = "0";
			if ( ! empty($completion_progress) && ! empty($time_elapsed) ) {
				$adherence_rate = round($completion_progress / ($time_elapsed / 180), 2);
				// $adherence_rate = round($completion_progress / ($time_elapsed / 180));
			}

			$all_adherence_rate[] = (string) $adherence_rate;

		}

		sort($all_adherence_rate);

		$count = count($all_adherence_rate);
	    $middle = floor($count / 2);

		 if ($count % 2 == 0) {
	        $median = ($all_adherence_rate[$middle - 1] + $all_adherence_rate[$middle]) / 2;
	    } else {
	        $median = $all_adherence_rate[$middle];
	    }
if($completed_courses_count != 0 ||  $total_users != 0){
        $completion = floor($completed_courses_count / $total_users * 100) . '%';
}else{
    $completion = '0%';
}


                }

                // Add other data fields as needed
                $median_grade_percentage = ''; // Add your logic to calculate median grade percentage
                $median_adherence_rate = number_format($median, 2);; // Add your logic to calculate median adherence rate
                $not_started = $not_started_count; // Add your logic to calculate not started
                $in_progress = $progress_courses_count; // Add your logic to calculate in progress
                $completed = $completed_courses_count; // Add your logic to calculate completed
                $avg_quiz_score = ''; // Add your logic to calculate average quiz score
                $percent_complete = $completion; // Add your logic to calculate percent complete

                // Adjust this part to fit your actual data structure
                $data[] = array(
                    'ID' => get_the_ID(),
                    'Course' => '<a href="' . admin_url('admin.php?page=course_users&course_id=' . $course_id) . '">' . get_the_title() . '</a>',
                    'Enrolled' => $total_users,
                    'MedianGradePercentage' => $median_grade_percentage,
                    'MedianAdherenceRate' => $median_adherence_rate,
                    'NotStarted' => $not_started,
                    'InProgress' => $in_progress,
                    'Completed' => $completed,
                    'AvgQuizScore' => $avg_quiz_score,
                    'PercentComplete' => $percent_complete,
                    'Details' => '<a href="' . get_permalink() . '">Details</a>',
                );
            }

            wp_reset_postdata();
        }

        // Return the JSON response
        // wp_send_json($data);
        echo json_encode(array('data' => $data));
        wp_die();
    }





public function course_users_ajax_callback() {
    //Handle Ajax request and fetch course user data
//     ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
function get_user_tags($user_id) {
    $user_tags = array();
    if( have_rows('user_tags', 'user_'. $user_id) ) {
        while( have_rows('user_tags', 'user_'. $user_id) ) {
            the_row();
            $user_tags[] = get_sub_field('select_user_tags');
        }
    }
    return $user_tags;
}

global $wpdb;
    $draw = intval($_REQUEST['draw']);
    $start = intval($_REQUEST['start']);
    $length = intval($_REQUEST['length']);

    // Other parameters for sorting, search, etc.
    $order = $_REQUEST['order'][0]['column'];
    $order_dir = $_REQUEST['order'][0]['dir'];
    $search = sanitize_text_field($_REQUEST['search']['value']);

    $quizAverage = '';
    $completionDate = '';
    $completedLessons = '';
    $cohortId = '';
    $classType = '';
    $cohortName = '';
    $cohortValue = '';
    $cohort_value = '';
    $status = '';
    $timer = '';
    $topics = '';
    $startDate = '';
    $endDate = '';
    $userTags = '';
    $timeElapsed = '';
    $adherenceRate = '';
    $quiz = '';
    $lastLogin = '';
    $percentComplete = '';
    $details = '';
    $today = new DateTime();



    // Fetch course users based on the parameters
    $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
    $filter_data = isset($_POST['selectedTags']) ? sanitize_text_field($_POST['selectedTags']) : '';

    if ($course_id) {
        $args = array(
            'number'    => $length ,
            'offset'    => $start,

        );
        // Modify this part based on your LearnDash setup
        $course_users_query = learndash_get_users_for_course($course_id, $args, false);

        if ($course_users_query) {
            $user_ids = $course_users_query->get_results();
            $total_users = $course_users_query->get_total();
            $data = array();

            $user_ids_rearranged = array();
		foreach ( $user_ids as $row_id => $user_id ) {
			$user_ids_rearranged[ $user_id ]             = array();
			$user_ids_rearranged[ $user_id ]['progress'] = 0;
			$user_ids_rearranged[ $user_id ]['date']     = array(
				'display'   => '',
				'timestamp' => '0',
			);
		}


		$complete_key = "course_completed_{$course_id}";

        if (isset($_POST['selectedTags'])) {
            // Sanitize input
            $selectedTags = array_map('sanitize_text_field', $_POST['selectedTags']);

            // Initialize meta_query array
            $meta_query = array();

            for ($index = 0; $index < 15; $index++) {
                // Construct meta query for each tag and each user_tags_x_select_user_tags field
                foreach ($selectedTags as $tag) {
                    $key = "user_tags_{$index}_select_user_tags";
                    $meta_query[] = array(
                        'key'     => $key,
                        'value'   => $tag,
                        'compare' => 'LIKE'
                    );
                }
            }

            // Combine all meta queries with 'relation' => 'OR'
            $meta_query['relation'] = 'OR';

            $args = array(
                'number'     => $length,
                'offset'     => $start,
                'meta_query' => $meta_query
            );

            // Retrieve users based on the constructed query
            $course_users = learndash_get_users_for_course($course_id, $args, false);
            if ($course_users) {
                $user_ids = $course_users->get_results();
            }
        }
        elseif (isset($_POST['min_date']) && isset($_POST['max_date'])) {

            // Get the provided start and end dates
            $min_date = strtotime($_POST['min_date']);
            $max_date = strtotime($_POST['max_date']);

            // Calculate the expiration date (180 days after the enrollment start date)
            $expiration_date = strtotime('+180 days', $min_date);

            // Initialize meta_query array
            $meta_query = array(
                'relation' => 'OR', // Users must satisfy both conditions
                array(
                    'key'     => 'course_'.$course_id.'_access_from', // Adjust the course ID dynamically
                    'value'   => array($min_date, $expiration_date),
                    'compare' => 'BETWEEN',
                    'type'    => 'NUMERIC' // Date values are stored as timestamps
                )
            );

            $args = array(
                'number'     => $length,
                'offset'     => $start,
                'meta_query' => $meta_query
            );

            // Retrieve users based on the constructed query
            $course_users = learndash_get_users_for_course($course_id, $args, false);

            if ($course_users) {
                $user_ids = $course_users->get_results();
            }
        }

         else {
            // If no tags are selected, retrieve all user IDs with specific meta keys
            $q = "SELECT DISTINCT user_id FROM {$wpdb->usermeta} WHERE meta_key = '_sfwd-course_progress' OR meta_key = '{$complete_key}'";
            $user_data = $wpdb->get_results($q);
        }



// echo "<pre>";
// print_r( $q );
// echo '<br>';
// print_r($user_ids);
// echo "</pre>";

		foreach ( $user_data as $data ) {
			$user_id = $data->user_id;

			if ( ! isset( $user_ids_rearranged[ $user_id ] ) ) {
				continue;
			}

			$meta_key   = $data->meta_key;
			$meta_value = $data->meta_value;

			if ( $complete_key === $meta_key ) {
				if ( absint( $meta_value ) ) {
					$user_ids_rearranged[ $user_id ]['date'] = array(
						'display'   => learndash_adjust_date_time_display( $meta_value ),
						'timestamp' => (string) $meta_value,
					);
				}
			} elseif ( '_sfwd-course_progress' === $meta_key ) {
				$progress = unserialize( $meta_value );
				if ( ! empty( $progress ) && ! empty( $progress[ $course_id ] ) && ! empty( $progress[ $course_id ]['total'] ) ) {
					$completed = intVal( $progress[ $course_id ]['completed'] );
					$total     = intVal( $progress[ $course_id ]['total'] );
					if ( $total > 0 ) {
						$percentage                                  = intval( $completed * 100 / $total );
						$percentage                                  = ( $percentage > 100 ) ? 100 : $percentage;
						$user_ids_rearranged[ $user_id ]['progress'] = $percentage;
					}
				}
			}
		}

        // echo "<pre>";
        // print_r($user_ids);
        // echo "</pre>";

         $quiz_averages = self::get_course_quiz_average_by_user( $course_id, $user_ids );
            foreach ($user_ids as $userid) {
                $user = get_user_by('id', $userid);

                // Add your logic to filter users based on search criteria
                // if (empty($search) || stripos($user->display_name, $search) !== false || stripos($user->user_email, $search) !== false) {
                    $userId = $user->ID;
                    $userName = $user->display_name;
                    $userEmail = $user->user_email;
                    $query = "
			SELECT COUNT(DISTINCT p.ID) AS total_lessons
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
			WHERE pm.meta_key = 'course_id' AND pm.meta_value = %d
			AND p.post_type = 'sfwd-lessons'
		";

		// Execute the query and get the result
		$total_lessons = $wpdb->get_var($wpdb->prepare($query, $course_id));


		$query = "
    SELECT COUNT(*) FROM {$wpdb->prefix}learndash_user_activity
    WHERE course_id = %d
    AND activity_type = 'lesson'
    AND activity_status = 1
    AND user_id = %d
";

// Execute the query and get the count
$completed_lessons_count = $wpdb->get_var($wpdb->prepare($query, $course_id, $userId));
$complition_percentage = ($completed_lessons_count * 100) / $total_lessons;

$query = "
			SELECT COUNT(DISTINCT p.ID) AS total_lessons
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
			WHERE pm.meta_key = 'course_id' AND pm.meta_value = %d
			AND p.post_type = 'sfwd-quiz'
		";

		// Execute the query and get the result
		$total_quiz = $wpdb->get_var($wpdb->prepare($query, $course_id));

		$query = "
    SELECT COUNT(*) FROM {$wpdb->prefix}learndash_user_activity
    WHERE course_id = %d
    AND activity_type = 'quiz'
    AND activity_status = 1
    AND user_id = %d
";

// Execute the query and get the count
$completed_quiz_count = $wpdb->get_var($wpdb->prepare($query, $course_id, $userId));

        // Last Login Code
        $last_login = get_user_meta($userId, 'last_login', true);

		if (empty($last_login)) {
			$last_login_time = 'N/A';
		}else{

            $last_login_time = date('F j, Y g:i a', $last_login);
            }

        $cohort_name = '';
        $class_type_text = '';
			$assigned_teacher_id = get_user_meta($userId, 'assigned_teacher_' . $course_id, true);
			$assigned_type_id = get_user_meta($userId, 'assigned_type_' . $course_id, true);
			if ($assigned_teacher_id) {
				global $wpdb;
				$table_name = $wpdb->prefix . 'cohort_users';
				$cohort = $wpdb->get_results("SELECT * from $table_name where id=$assigned_teacher_id ");
				if(!empty($cohort)) {
				    $cohort_name = $cohort[0]->name;

				}

				}
				if ($assigned_type_id) {
				$table_type = $wpdb->prefix . 'class_type';
				$class_type = $wpdb->get_results("SELECT * from $table_type where id=$assigned_type_id ");
				if(!empty($class_type)) {
				    $class_type_text = $class_type[0]->type_title;
				}
			}


		$start_date = date('Y-m-d', ld_course_access_from($course_id, $userId));
		$d2 = new DateTime($start_date);
		$interval = $d2->diff($today);
		$time_elapsed = $interval->format('%a'); //day

		$progress = learndash_user_get_course_progress($userId, $course_id);
		$completion_progress = round((100 * $progress['completed']) / $progress['total']);

		$adherence_rate = "";
		if ( ! empty($completion_progress) && ! empty($time_elapsed) ) {
			// $adherence_rate = round(($completion_progress / $time_elapsed) * 100);
			// $adherence_rate = min(100, number_format(($completion_progress / 50) * ($time_elapsed / 180) * 100, 2));
			// $adherence_rate = min(number_format($completion_progress / ($time_elapsed / 180), 2, ".", ""), 100);
			$adherence_rate = number_format($completion_progress / ($time_elapsed / 180));

		}

		if ($adherence_rate) {
			$adherence_rate .= '%';
		}

$quiz_average = '';
        if ( isset( $quiz_averages[ $userId ] ) ) {
            $quiz_average = $quiz_averages[ $userId ].'%';
        }

        $query = "
        SELECT COUNT(DISTINCT p.ID) AS total_lessons
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
        WHERE pm.meta_key = 'course_id' AND pm.meta_value = %d
        AND p.post_type = 'sfwd-topic'
    ";

    // Execute the query and get the result
    $total_topics = $wpdb->get_var($wpdb->prepare($query, $course_id));

    $query = "
SELECT COUNT(*) FROM {$wpdb->prefix}learndash_user_activity
WHERE course_id = %d
AND activity_type = 'topic'
AND activity_status = 1
AND user_id = %d
";

// Execute the query and get the count
$completed_topic_count = $wpdb->get_var($wpdb->prepare($query, $course_id, $user_id));



//Enrolled Timer

$enrolledtable_name = $wpdb->prefix . 'learndash_user_activity';

$query = $wpdb->prepare("
    SELECT activity_started
    FROM $enrolledtable_name
    WHERE user_id = %d
    AND course_id = %d
    AND activity_type = %s
", $user_id, $course_id, 'access');

$enrollment_date = $wpdb->get_var($query);
$days_difference = '';
$endDate = '';
if ($start_date) {

	$newTimestamp = strtotime('+180 days', strtotime($start_date));

	// Convert the new timestamp to a date
	$endDate = date('Y-m-d', $newTimestamp);

    $enrollment_date_timestamp = ($enrollment_date);
    $current_date_timestamp = current_time('timestamp', true); // Get the current date as a timestamp

    $days_difference = round(($current_date_timestamp - $enrollment_date_timestamp) / (60 * 60 * 24));
	$days_difference = 180 - $days_difference;
}


if ($assigned_type_id) {
$table_type = $wpdb->prefix . 'class_type';
$class_type = $wpdb->get_results("SELECT * from $table_type where id=$assigned_type_id ");
if(!empty($class_type)) {
    $class_type = $class_type[0]->type_title;
    $rows[ $row_id ]['class_type'] = $class_type;
	if($enrollment_date){
		$formatted_date = date('M-y', ($enrollment_date));
		$cohort_value = $class_type.'-'.$formatted_date;
	}
}

			}


$userTags = implode(", ", get_user_tags($userId)) ;


$status = get_user_meta($userId,'user_status',true);

$rowData[] = array(
    'userName' => $userName,
    'userEmail' => $userEmail,
    'quizAverage' => $quiz_average, // Add your logic for Quiz Average
    'completionDate' => $user_ids_rearranged[ $userId ]['date']['display'], // Add your logic for Completion Date
    'completedLessons' => $completed_lessons_count . '/' . $total_lessons,
    'classType' => $class_type_text, // Add your logic for Class Type
    'cohortId' => $course_id.$userId, // Add your logic for Cohort ID
    'cohortName' => $cohort_name, // Add your logic for Cohort Name
    'cohortValue' => $cohort_value,
    'status' => $status,// Add your logic for Cohort Value
    'timer' => $days_difference.' Days', // Add your logic for Timer
    'topics' => $completed_topic_count.'/'.$total_topics, // Add your logic for Topics
    'startDate' => $start_date, // Add your logic for Start Date
    'endDate' => $endDate, // Add your logic for End Date
    'userTags' => $userTags, // Add your logic for End Date
    'timeElapsed' => $time_elapsed, // Add your logic for Time Elapsed
    'completionPercentage' => $complition_percentage . '%',
    'adherenceRate' => $adherence_rate, // Add your logic for Adherence Rate
    'quiz' => $completed_quiz_count.'/'.$total_quiz, // Add your logic for Quiz
    'lastLogin' => $last_login_time, // Add your logic for Last Login
    'percentComplete' => $user_ids_rearranged[ $userId ]['progress'].'%', // Add your logic for % Complete
);

               // }
            }

            $total_records = count($rowData);
            $filtered_data = array_slice($rowData, $start, $length);

            $response = array(
                'draw'            => $draw,
                'recordsTotal'    => $total_users,
                'recordsFiltered' => $total_users,
                'data'            => $rowData,
            );

            echo json_encode($response);
       }
   }

    wp_die();
}

/**
	 * @param $course_id
	 * @param $user_ids
	 *
	 * @return array
	 */
	private static function get_course_quiz_average_by_user( $course_id, $user_ids ) {

		global $wpdb;

		$user_ids_rearranged = array();
		foreach ( $user_ids as $user_id ) {
			$user_ids_rearranged[ $user_id ] = $user_id;
		}

		$q_quiz_results = "
			SELECT a.course_id, a.post_id, m.activity_meta_value as activity_percentage, a.user_id
			FROM {$wpdb->prefix}learndash_user_activity a
			LEFT JOIN {$wpdb->prefix}learndash_user_activity_meta m ON a.activity_id = m.activity_id
			WHERE a.activity_type = 'quiz'
			AND a.course_id = $course_id
			AND m.activity_meta_key = 'percentage'
		";

		$quiz_results = $wpdb->get_results( $q_quiz_results );

		$quiz_scores = array();

		foreach ( $quiz_results as $activity ) {

			if ( isset( $user_ids_rearranged[ (int) $activity->user_id ] ) ) {

				if ( ! isset( $quiz_scores[ $activity->user_id ] ) ) {
					$quiz_scores[ $activity->user_id ] = array();
				}

				if ( ! isset( $quiz_scores[ $activity->user_id ][ $activity->post_id ] ) ) {
					$quiz_scores[ $activity->user_id ][ $activity->post_id ] = $activity->activity_percentage;
				} elseif ( $quiz_scores[ $activity->user_id ][ $activity->post_id ] < $activity->activity_percentage ) {
					$quiz_scores[ $activity->user_id ][ $activity->post_id ] = $activity->activity_percentage;
				}
			}
		}

		$averages = array();
		if ( 0 !== count( $quiz_scores ) ) {
			foreach ( $quiz_scores as $user_id => $scores ) {
				$averages[ $user_id ] = absint( array_sum( $scores ) / count( $scores ) );
			}
		}

		return $averages;
	}


public function update_user_role_callback() {
    // Check nonce for security
    //check_ajax_referer('update_user_role_nonce', 'nonce');

    // Get user ID and role from the AJAX request
    $email = isset($_POST['email']) ? ($_POST['email']) : 0;
    $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';

    // Perform the update here
    // Example: update_user_meta($user_id, 'role', $role);
    $user = get_user_by('email',$email);

if($user ){
    update_user_meta($user->ID, 'user_status', $status);
    echo json_encode(array('success' => true));
}
    // Send a response (you can customize this based on your needs)


    // Always exit to avoid extra output
    wp_die();
}


}
