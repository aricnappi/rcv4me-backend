<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

declare(strict_types=1);

/**
 * An object holding the ranked choices submitted by a user.
 */
class Ballot {
    /**
     * An internal array that holds the candidate names in ranked order.
     * @var array[string]
     */
    private $ballotArr = [];

    /**
     * Create a new Ballot.
     * @param array[string] $rankedChoices The choices submitted by the user in the 
     *     order they were ranked (starting at index 1).
     */
    public function __construct(array $rankedChoices) {
        $this->ballotArr = $rankedChoices;
    }
    
    /**
     * Get the number of candidates that were ranked by the user when they 
     * submitted their ballot.
     * @return int The number of candidates ranked on this Ballot.
     */
    public function getNumberRanked(): int {
        return count($this->ballotArr);
    }
    
    /**
     * Get the name of the active candidate for which this Ballot is voting.
     * @param array[string] $activeCandidates An (unordered) array of the names of the candidates who
     *     have not been eliminated yet in the contest.
     * @return string The name of the highest-ranked, non-eliminated candidate on this
     *     Ballot.
     */
    public function getVote(array $activeCandidates) : string {
        // Get the Candidates on this Ballot who are active.
        $activeCandidatesOnThisBallot = array_intersect($this->ballotArr, $activeCandidates);
        // Sort them by rank
        ksort($activeCandidatesOnThisBallot);
        // Reindex the active candidates on this ballot so the indices start at 0
        $activeCandidatesOnThisBallot = array_values($activeCandidatesOnThisBallot);
        // if not all candidates on this Ballot have been eliminated
        if (!empty($activeCandidatesOnThisBallot)) {
            // Return the highest-ranked Candidate.
            return $activeCandidatesOnThisBallot[0];
        }
        // if all candidates on this Ballot have been eliminated
        else {
            // this Ballot does not have a vote
            return '';
        }
    }
}