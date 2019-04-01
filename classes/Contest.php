<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

declare(strict_types=1);
include 'Round.php';

/**
 * A class representing a ranked-choice voting (i.e. instant runoff) contest.
 */
class Contest {
    // TODO: put in a check to see if all candidates were eliminated (such as in
    // some tie situations) before proceeding to the next round.
    
    /**
     * All of the Rounds of the Contest.
     * @var array 
     */
    private $rounds = [];
    
    /**
     * All of the Piles corresponding to each of the candidates in the Contest.
     * @var array 
     */
    private $piles = [];
    
    /**
     * The names of the candidates that have not been eliminated yet.
     * @var array[string]
     */
    private $activeCandidates;
    
    /**
     * The names of all of the candidates in the Contest.
     * @var array 
     */
    private $allCandidates;
    
    /**
     * Create and initialize a new RCV contest.
     * @param array $rawBallots An array wherein each entry is an array with
     *     candidates names in the order they were ranked by the users who 
     *     submitted the corresponding ballots.
     * @param array $candidates The names of all of the candidates in the contest.
     */
    public function __construct(array $rawBallots, array $candidates) {
        // Store the list of candidates
        $this->allCandidates = $candidates;
        // Activate all of the candidates
        $this->activeCandidates = $candidates;
        // Create a Pile for each one
        foreach($candidates as $candidate) {
            // Create the Pile
            $pile = new Pile($candidate);
            // Add it to the list of Piles
            $this->piles[$candidate] = $pile;
        }
        // Create the Pile initially containing all of the Ballots
        $ballots = [];
        // For each raw ballot
        foreach($rawBallots as $rawBallot) {
            // Make a Ballot object
            $ballot = new Ballot($rawBallot);
            // Add it to the list of ballots
            $ballots[] = $ballot;
        }
        // Create a new Pile
        $initialPile = new Pile('__initialPile');
        // Add all of the Ballots to it
        $initialPile->addBallots($ballots);
        // Store it in the list of Piles
        $this->piles['__initialPile'] = $initialPile;
    }
    
    /**
     * Runs the RCV algorithm with the ballots and candidates given at 
     * construction.
     * @return void
     */
    public function runContest() : void {
        // distribute the initial Pile of Ballots
        $this->eliminateCandidates();
        do {
            // Start a new Round
            $newRound = $this->createNewRound();
            // Add it to the list of Rounds
            $this->rounds[] = $newRound;
            // update list of active candidates
            $this->activeCandidates = $newRound->getNonEliminatedCandidates();
            // Eliminate candidates who are no longer active
            $this->eliminateCandidates();
        // Repeat this until all candidates have been eliminated
        } while (!empty($this->activeCandidates));
    }
    
    /**
     * Creates a new Round of the Contest with the Piles of the active candidates.
     * @param array $piles The Piles of the candidates in the new Round.
     * @return Round A new Round with the Piles of the candidates.
     */
    public function createNewRound() {
        // Get the Piles of the active candidates
        $activeCandidatesPiles = array_filter($this->piles, function($pile) {
            return in_array($pile->getName(), $this->activeCandidates);
        });
        // Create a new Round with these Piles
        $newRound = new Round($activeCandidatesPiles);
        // This is the new Round of the Contest; return it
        return $newRound;
    }
    
    /**
     * Redistribute the Ballots of any candidates that are no longer active 
     * (i.e. that have been eliminated);
     * @return void
     */
    private function eliminateCandidates() : void {
        // for each Pile
        foreach($this->piles as $candidate => $pile) {
            // if its corresponding candidate has been eliminated
            if (!in_array($candidate, $this->activeCandidates)) {
                // redistribute its ballots to the Piles of the active candidates
                $this->redistributeBallots($pile);
            }
        }
    }
    
    /**
     * Redistributes the Ballots in the given Pile to the Piles of the active
     * candidates.
     * @param Pile $pile The Pile whose Ballots are to be redistributed.
     */
    private function redistributeBallots(Pile $pile) {
        // for each Ballot in the Pile
        foreach($pile as $ballot) {
            // Get the candidate for which it is voting
            $vote = $ballot->getVote($this->activeCandidates);
            // if this Ballot is casting a vote
            if (!empty($vote)) {
                // Get the corresponding Pile
                $votePile = $this->piles[$vote];
                // Add the Ballot to the Pile
                $votePile->addBallot($ballot);
            }
            // else the Ballot is ignored
        }
    }
    
    /**
     * Get the distributions of votes among the candidates for each Round.
     * @return array The distributions of votes in each round as associative 
     *     arrays
     */
    public function getVoteDistributions() : array {
        $voteDistributions = [];
        // for each Round in the Contest
        foreach($this->rounds as $round) {
            // Get the vote distribution of the Round
            $roundVotes = $round->getVoteDistribution();
            // Add it to the list of vote distributions
            $voteDistributions[] = $roundVotes;
        }
        // Return the vote distributions
        return $voteDistributions;
    }
    
    /**
     * Get the winner of the Contest. The winner is the candidate that got the 
     * most votes in the last Round. If two or more candidates tie for first in
     * the last Round, the candidate among them that got the most votes in the 
     * second-to-last Round is the winner. If there is a tie among them in the 
     * second-to-last Round, the process repeats until a winner is found. If no 
     * winner is found through this process, then there is no winner. If all 
     * candidates tie in the first Round, there is no winner.
     * @return string The winner of the Contest. Is the empty string if there is
     *     no winner.
     */
    public function getWinner() : string {
        // Initialize the tracker of top candidates
        $topCandidates = $this->allCandidates;
        // Initialize the Round tracker
        $roundNum = count($this->rounds)-1;
        // While there is a tie and there are still Rounds left to check
        while ((count($topCandidates) !== 1) && $roundNum >= 0) {
            // Get the Round currently being analyzed
            $thisRound = $this->rounds[$roundNum];
            // Get the candidates among the top candidates with the most votes 
            // in this round; this is the new list of top candidates
            $topCandidates = $thisRound->findTopCandidates($topCandidates);
            // Set next iteration to consider the next Round
            $roundNum--;
        }
        // If the tie is broken
        if (count($topCandidates) === 1) {
            // return the winner
            return $topCandidates[0];
        }
        // If the tie is not broken
        else {
            // there is no winner
            return 'It\'s a tie! No winner!';
        }
    }
}