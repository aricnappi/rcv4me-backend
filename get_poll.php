<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

// Connect to database
include '/var/www/html/phpscripts/common/initialization.php';

// Get poll ID
$pollID = $_GET['id'];

// Get row from database
$sql = "SELECT question, options FROM `polls` WHERE poll_id = '$pollID'";
$result = $mysqli->query($sql);
$row = $result->fetch_assoc();
// Put row data in output variable
// The string $row['options'] needs to be decoded so that when it is encoded as part of $pollData, it is encoded as an array and not as a string.
// If you do not decode it here, JSON.parse in Javascript will interpret $pollData['options'] as a string instead of an array as intended.
$pollData = ['question' => $row['question'], 'options' => json_decode($row['options'])];
// Shuffle the options into a random order
// shuffle($pollData['options']);
echo json_encode($pollData);

$mysqli->close();