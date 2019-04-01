<?php
declare(strict_types=1);
include 'classes/Contest.php';


/**
 * Get the ballot data from the database.
 * @param mysqli $mysqli A mysqli database object with an open connection.
 * @param string $pollID The ID of the poll whose ballot data are being requested.
 * @return array The ballot data.
 */
function getBallotData(mysqli $mysqli, string $pollID) : array {
    $ballotsSQL = "SELECT `ballots` FROM `ballots` WHERE `poll_id` = '$pollID'";
    $ballotsResult = $mysqli->query($ballotsSQL);
    $rawBallots = $ballotsResult->fetch_all(MYSQLI_NUM);
    $ballotDataArr =[];
    foreach($rawBallots as $row) {
        $ballotData = (array) json_decode($row[0]);
        array_push($ballotDataArr, $ballotData);
    }
    return $ballotDataArr;
}

/**
 * Get the list of all candidates from the database.
 * @param mysqli $mysqli A mysqli database object with an open connection.
 * @param string $pollID The ID of the poll whose ballot options are being requested.
 * @return array[string] The names of the candidates.
 */
function getCandidates(mysqli $mysqli, string $pollID) : array {
    $optionsSQL = "SELECT `options` FROM `polls` WHERE `poll_id` = '$pollID'";
    $optionsResult = $mysqli->query($optionsSQL);
    $optionsData = $optionsResult->fetch_all(MYSQLI_NUM);
    $options = json_decode($optionsData[0][0]);
    return $options;
}

/**
 * Get the question of the poll corresponding to the given ID.
 * @param mysqli $mysqli A mysqli object with an open connection.
 * @param string $pollID The ID of the poll.
 * @return string The question of the poll.
 */
function getPollQuestion(mysqli $mysqli, string $pollID) : string {
    // Get row from database
    $sql = "SELECT question FROM `polls` WHERE poll_id = '$pollID'";
    $result = $mysqli->query($sql);
    $row = $result->fetch_assoc();
    return $row['question'];
}


// Connect to the database
include '/var/www/html/phpscripts/common/initialization.php';
// Get the ID of the poll whose results were requested
$pollID = $_GET['id'];
// Get the raw ballot data from the database
$ballotData = getBallotData($mysqli, $pollID);
// Get the list of candidates from the database
$options = getCandidates($mysqli, $pollID);
// Initalize the contest
$contest = new Contest($ballotData, $options);
// Run the contest
$contest->runContest();
// Get the winner
$winner = $contest->getWinner();
// Get the distributions of votes by round
$voteDistributions = $contest->getVoteDistributions();
// Sort each round so the candidates with the highest vote totals are first
//foreach($voteDistributions as $roundNum => $round) {
//    // Create a copy of the round data
//    $roundCopy = $round;
//    // Sort it by value while keeping the keys
//    arsort($roundCopy);
//    // Replace the unsorted round data with the sorted round data
//    $voteDistributions[$roundNum] = $roundCopy;
//}
// Get the poll's question
$question = getPollQuestion($mysqli, $pollID);
// Create the output object
$output = ['question' => $question, 'winner' => $winner, 'rounds' => $voteDistributions];
// Encode it and send it to the client
echo json_encode($output);
// Close the database connection
$mysqli->close();
