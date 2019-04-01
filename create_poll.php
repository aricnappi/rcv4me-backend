<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include 'classes/IDCode.php';

/**
 * Determines whether the question string submitted by the user is valid.
 * It is valid if it has between 1 and 50 characters, inclusive on each end.
 * @param string $question The submitted poll question.
 * @return bool Whether the question is valid.
 */
function validateQuestion(string $question) : bool {
    $stringLength =  strlen($question);
    return ($stringLength >= 1) && ($stringLength <= 50);
}

/**
 * Determines whether the candidates/options strings submitted by the user are 
 * valid.
 * They are valid if they are between 1 and 25 characters, inclusive on each end.
 * They are invalid if there are less than two candidates.
 * @param array[string] $candidates The candidates submitted by the user.
 * @return bool Whether all of the candidate strings are valid.
 */
function validateCandidates(array $candidates) : bool {
    // If the list of candidates has less than two entries
    if (count($candidates) < 2) {
        // The list is invalid
        return false;
    }
    $candidatesAreValid = true;
    // For each candidate string
    foreach($candidates as $candidate) {
        // If the string is not long or short enough
        $stringLength = strlen($candidate);
        if (!(($stringLength >= 1) && ($stringLength <= 25))) {
            // the submitted list of candidates is invalid
            $candidatesAreValid = false;
            break;
        }
    }
    return $candidatesAreValid;
}


// Get the sent data
$question = $_POST['question'];
$candidates = json_decode($_POST['options']);
// Determine if the sent data is valid
$questionIsValid = validateQuestion($question);
$candidatesAreValid = validateCandidates($candidates);
// If it is valid
if ($questionIsValid && $candidatesAreValid) {
    // Connect to the database
    include '/var/www/html/phpscripts/common/initialization.php';
    // Generate a unique ID code
    $IDCode = new IDCode($mysqli);
    // Get it as a string
    $pollID = $IDCode->getIDCodeString();
    // SQL query template that inserts data into database
    $stmtString = "INSERT INTO `polls` (`poll_id`, `question`, `options`) VALUES (?, ?, ?)";
    // Prepare the statement template
    $stmt = $mysqli->prepare($stmtString);
    // Bind the poll ID, question, and candidates as parameters
    $candidatesEncoded = json_encode($candidates);
    $stmt->bind_param('sss', $pollID, $question, $candidatesEncoded);
    // Send the poll ID, question, and candidates to the database
    $stmt->execute();
    // Return the poll ID to the client-side script
    echo $pollID;
    // Close the database connection
    $mysqli->close();
}
// If the sent data is not valid
else {
    // 422 Unprocessable Entity
    http_response_code(422);
    // Send back error message
    echo "The poll you submitted is not valid. Please try again.";
//    exit();
}