<?php

function delete_feedback($id) {
		global $mysqli;

		$query = $mysqli->prepare("DELETE FROM feedback WHERE FeedbackID = ?");
		$query->bind_param("i", $id);
		if(!$query->execute()) throw new Exception($mysqli->error);

}