<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

declare(strict_types=1);
include 'Ballot.php';

/**
 * A class representing the collection (pile) of Ballots cast for a candidate in
 * the contest.
 */
class Pile implements Iterator {
    /**
     * The name of the candidate corresponding to this Pile of Ballots.
     * @var string
     */
    private $name = '';
    
    /**
     * The Ballots in this Pile.
     * @var array[Ballot]
     */
    private $ballots = [];
    
    /**
     * Creates a new Pile of Ballots with the given name of the candidate to which this Pile
     * will correspond. The name must be unique among all candidates in the contest.
     * @param string $candidateName The unique name of the candidate.
     * @return void
     */
    public function __construct(string $candidateName) {
        $this->name = $candidateName;
    }
    
    /**
     * Get the name of the candidate corresponding to this Pile of Ballots.
     * @return string The name of this Candidate.
     */
    public function getName() : string {
        return $this->name;
    }
    
    /**
     * Add several Ballots to this Pile. If a Ballot already exists in this Pile, it 
     * will not be added again.
     * @param array[Ballot] $ballots The Ballots to be added to this Pile.
     * @return void
     */
    public function addBallots(array $ballots) : void {
        foreach($ballots as $ballot) {
            $this->addBallot($ballot);
        }
    }
    
    /**
     * Add a single Ballot to this Pile. If a Ballot already exists in this Pile, it 
     * will not be added again.
     * @param Ballot $ballot The Ballot to be added to this Pile.
     * @return void
     */
    public function addBallot(Ballot $ballot) : void {
        // If the ballot to be added is not already in the pile
        if (!in_array($ballot, $this->ballots, true)) {  // strict check
            // Add it to the pile
            array_push($this->ballots, $ballot);
        }
        // else do not add it
    }
    
    /**
     * Get the number of Ballots in this Pile.
     * @return int The total number of Ballots in this Pile.
     */
    public function getTotalBallots() : int {
        return count($this->ballots);
    }
    
    // Iterator methods implemented below
    
    private $position = 0;
    
    public function rewind() {
        $this->position = 0;
    }
    
    public function current() {
        return $this->ballots[$this->position];
    }
    
    public function key() {
        return $this->position;
    }
    
    public function next() {
        ++$this->position;
    }
    
    public function valid() {
        return isset($this->ballots[$this->position]);
    }
}