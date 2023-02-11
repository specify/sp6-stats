<?php

const DATABASE = 'feedback';

require_once('../components/header.php');
require_once('../config/cache.php');
require_once('../components/Cache_query.php');

// Delete feedback
if(array_key_exists('delete',$_GET) && strlen($_GET['delete']) > 0) {
  require_once('./delete.php');
  delete_feedback(intval($_GET['delete']));
}

require_once('../refresh_data/feedback.php');

$number_of_results = count($data);

$cache->get_status(LINK,'feedback',$number_of_results); ?>

<table class="table table-striped mt-5 mb-5"> <?php

if($number_of_results>0) { ?>

    <thead>
      <tr> <?php
        foreach(array_keys($data[0]) as $header)
          echo "<th>$header</th>"; ?>
        <th>Delete</th>
      </tr>
    </thead>
    <tbody> <?php

}


foreach($data as $feedback){
  echo '<tr>';

  foreach($feedback as $value)
    echo '<td>'.$value.'</td>';

  echo '<td><a class="btn btn-danger" href="'.LINK.'feedback/?update_cache=true&delete='.$feedback['FeedbackID'].'">X</a></td>';
  echo '</tr>';
}

echo '</tbody></table>';

footer();