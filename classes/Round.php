<?php
declare(strict_types=1);
include 'Pile.php';

/**
 * A class representing the vote distributions of a round of a Contest.
 */
class Round {
    /**
     * An associative array with the distribution of the votes for this round.
     * @var array[int] 
     */
    private $voteDistribution;
    
    /**
     * Create a new Round of the Contest.
     * @param array[Pile] $piles The Piles of Ballots for each candidate in this
     *     Round of the Contest.
     */
    public function __construct(array $piles) {
        // Calculate and store the vote distribution among the candidates of the
        // given Piles
        $this->voteDistribution = $this->calculateVoteDistribution($piles);
    }
    
    /**
     * Calculate the number of votes for each candidate corresponding to a given Pile.
     * Each Ballot in a Pile counts as one vote.
     * @param array[Pile] The Piles from which the vote distribution is to be
     *     calculate.
     * @return array[int] An associative array consisting of the vote totals for
     *     each candidate.
     */
    private function calculateVoteDistribution(array $piles) : array {
        $voteDistribution = [];
        foreach($piles as $pile) {
            // Get the name of the candidate
            $name = $pile->getName();
            // Get their total number of votes
            $total = $pile->getTotalBallots();
            // Store it in $voteTotals
            $voteDistribution[$name] = $total;
        }
        return $voteDistribution;
    }
    
    /**
     * Get the number of votes for each candidate this Round.
     * Each Ballot in a candidate's Pile counts as one vote.
     * @return array[int] An associative array consisting of the vote totals for
     *     each candidate.
     */
    public function getVoteDistribution() : array {
        return $this->voteDistribution;
    }
    
    /**
     * Get the number of votes of a particular candidate in this Round. Any 
     * candidate not in this Round will report as having zero votes.
     * @param string $candidate The name of the candidate.
     * @return int The number of votes.
     */
    public function getVotes(string $candidate) : int {
        if (array_key_exists($candidate, $this->voteDistribution)) {
            return $this->voteDistribution[$candidate];
        } else {
            return 0;
        }
    }
    
    /**
     * Get the names of the candidates who were not eliminated this Round, i.e.
     * the candidates who did not have the least number of votes.
     * @return array[string] The names of the candidates who were not eliminated.
     */
    public function getNonEliminatedCandidates() : array {
        // Get the number of votes for each candidate
        $voteDistribution = $this->voteDistribution;
        // Get the vote threshold above which candidates are not eliminated
        $threshold = min($voteDistribution);
        // Get the vote distribution among the candidates whose votes exceed the threshold
        $nonEliminatedCandidatesVoteDistribution = array_filter($voteDistribution, function($votes) use($threshold){
            return $votes > $threshold;
        });
        // Get the names of the candidates whose votes exceed the threshold
        $nonEliminatedCandidates = array_keys($nonEliminatedCandidatesVoteDistribution);
        // These are the non-eliminated candidates; return them
        return $nonEliminatedCandidates;
    }
    
    /**
     * Get the candidate(s) among the given candidates with the most votes in 
     * this Round.
     * @param array[string] $candidates A (nonempty) list of candidates.
     * @return array[string] The top candidates among the given candidates.
     */
    public function findTopCandidates(array $candidates) : array {
        // Get the vote distribution among the given candidates
        $voteDistributionFiltered = array_filter($this->voteDistribution, function($key) use($candidates) {
            return in_array($key, $candidates);
        }, ARRAY_FILTER_USE_KEY);
        // Get the number of votes of the candidate(s) with the highest number
        // of votes
        $maxVotes = max($voteDistributionFiltered);
        // Initialize the top candidates list
        $topCandidates = [];
        // for each candidate under consideration
        foreach($candidates as $candidate) {
            // if they are active in this Round and their total number of votes 
            // is equal to the max number of votes
            if ($this->getVotes($candidate) === $maxVotes) {
                // they are a top candidate
                $topCandidates[] = $candidate;
            }
        }
        // return the list of top candidates
        return $topCandidates;
    }
}
