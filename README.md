# rcv4me-backend
The backend PHP code powering rcv4me.info.

## Why

My state, Maine, has voted to implement ranked-choice voting (i.e. instant runoff) for certain state and federal elections. I thought the algorithm was cool and so I wanted to create a site that implements that algorithm. I also want to educate people on how it works and allow them to participate in it and visualize the process.

## Algorithm
The scripts that implement the ranked-choice voting algorithm can be found in the 'classes' subfolder. Start with 'Contest.php'. All other scripts are just interfaces for the frontend.

This is an implementation of the algorithm used in ranked-choice/instant runoff voting. It follows Robert's rules of order, albeit slightly generalized to account for edge cases that occur when there is a small number of ballots.

The idea is that, given a list of candidates, you rank them in order of preference. For example, if you are voting for President, your choices might be 'Mr. Red', 'Mrs. Blue', 'Ms. Green', and 'Dr. Yellow'. You would then select your first choice candidate for President, then your second choice, third choice, and fourth choice. Your ballot is then submitted and the results are tabulated based on all submitted ballots.

The algorithm goes through all of the ballots and sorts them into piles based on their first-choice candidate. If your first choice was Ms. Green, your ballot would be placed into Ms. Green's pile. Then the candidate whose pile has the smallest number of ballots is eliminated. In the next round, the ballots are then redistributed to the other piles according to their second-choice candidate. This process continues through several rounds until there is only one pile left. The candidate to whom this pile belongs is the winner.

## Questions

### What if there is a tie for which candidate should be eliminated?

Both candidates are eliminated and both of their piles are redistributed.

### What if the next choice on my ballot has already been eliminated?

The algorithm skips past all eliminated candidates and finds the first one that has not been eliminated. If all of the candidates on your ballot have been eliminated, your ballot is discarded (sorry).

### What if there is a tie for the winner?

Whichever candidate had the most votes/ballots in the previous round is the winner. If this also results in a tie, then the round previous to that is considered. If two or more candidates tie in every round, then the contest is a tie.
