<?php
/**
 * Determines whether a given string is a valid poll ID. It is valid if there 
 * exists a poll in the database with that ID.
 * @param string $pollID The ID string to be validated.
 * @param mysqli $mysqli A mysqli object with an open connection.
 * @return bool Whether the given string is a valid poll ID.
 */
function validatePollID (string $pollID, mysqli $mysqli) : bool {
    // Get the IDs of all of the polls in the database
    // Create the sql query
    $pollSQL = 'SELECT `poll_id` FROM `polls`';
    // Send the query
    $result = $mysqli->query($pollSQL);
    // Get the column
    $pollsRaw = $result->fetch_all();
    // Take each column entry and put it in a list
    $polls = [];
    foreach($pollsRaw as $row) {
        $polls[] = $row[0];
    }
    // return true if the given poll ID is in the list of IDs of existing polls
    return in_array($pollID, $polls);
}

/**
 * Checks if the given ballot entries are a valid configuration of entries.
 * A configuration of ballot entries is valid if:
 * 1. the entries are ranked in ascending order starting at 1 without gaps (e.g. 1,2,4 is invalid);
 * 2. each entry is entered only once (e.g. 1:red, 2:green, 3:red is invalid).
 * @param array[string] $ballotEntries The ballot entries.
 * @param mysqli $mysqli A mysqli object with an open connection.
 * @return boolean Whether the entries are valid.
 */
function validateBallotEntries($ballotEntries, $pollID, $mysqli) {
    // If the ballot is blank
    if (empty($ballotEntries)) {
        // it is not valid
        return false;
    }
    // Sort the ballot entries in ascending order by key
    ksort($ballotEntries);
    // Initialize variables
    $previousRank = 0;
    $enteredCandidates = [];
    $ballotEntriesAreValid = true;
    // Get the poll candidates
    $sql = "SELECT `options` FROM `polls` WHERE `poll_id` = '$pollID'";
    $result = $mysqli->query($sql);
    $row = $result->fetch_row();
    $validCandidates = json_decode($row[0]);
    // for each entry on the ballot
    foreach($ballotEntries as $rank => $candidate) {
        // if its rank is a numeric value and this numeric value is one more than the previous rank, the rank is valid
        $rankIsValid = (is_numeric($rank)) && ($rank == $previousRank + 1);  // check value only (==), not type (===)
        // if the candidate string is among the candidates in the poll and it has not already been entered
        $candidateIsValid = (in_array($candidate, $validCandidates) && !in_array($candidate, $enteredCandidates));
        // if both rank and candidate entries are valid
        if ($rankIsValid && $candidateIsValid) {
            // move on to the next entry
            $previousRank = $rank;
            $enteredCandidates[] = $candidate;
        }
        // if either the rank or candidate entry is not valid
        else {
            // the ballot entries (and thus the entire ballot) are invalid
            $ballotEntriesAreValid = false;
            break;
        }
    }
    return $ballotEntriesAreValid;
}

function invalidData() {
    // 422 Unprocessable Entity
    http_response_code(422);
    // Send back error message
    echo "There was an error when processing the ballot data.";
    // Close the database connection
    global $mysqli;
    $mysqli->close();
    exit();
}

// Connect to the database
include '/var/www/html/phpscripts/common/initialization.php';
// Get the poll ID
$pollID = $_POST['id'];
// Get entries from submitted data
$ballotEntries = $_POST;
// Remove the id from the entries array
unset($ballotEntries['id']);
// Validate poll ID
$pollIDIsValid = validatePollID($pollID, $mysqli);
// If the poll ID is valid
if ($pollIDIsValid) {
    // Validate ballot entries
    $ballotEntriesAreValid = validateBallotEntries($ballotEntries, $pollID, $mysqli);
}
// If the poll ID is not valid
else {
    invalidData();  // exits the script
}
// If the ballotEntries are also valid
if ($ballotEntriesAreValid) {
    // SQL query template that inserts data into database
    $stmtString = "INSERT INTO `ballots` (`poll_id`, `ballots`) VALUES (?, ?)";
    // Prepare the statement template
    $stmt = $mysqli->prepare($stmtString);
    // Bind the poll ID and ballot entries as parameters
    $stmt->bind_param('ss', $pollID, json_encode($ballotEntries));
    // Send the poll ID and ballot entries to the database
    $stmt->execute();
}
// If the ballot entries are not valid
else {
    invalidData();
}
$mysqli->close();
