# rcv4me-backend
The backend PHP code powering rcv4me.info.

## Algorithm
The scripts that implement the ranked-choice voting algorithm can be found in the 'classes' subfolder. Start with 'Contest.php'. All other scripts are just interfaces for the frontend.

This is an implementation of the algorithm used in ranked-choice/instant runoff voting. It follows Robert's rules of order, albeit slightly generalized to account for edge cases that occur when there is a small number of ballots.

The idea is that, given a list of candidates, you rank them in order of preference. For example, if you are voting for President, your choices might be 'Mr. Red', 'Mrs. Blue', 'Ms. Green', and 'Dr. Yellow'. You would then select your first choice candidate for President, then your second choice, third choice, and fourth choice. Your ballot is then submitted and the results are tabulated based on all submitted ballots.

The algorithm goes through all of the ballots and sorts them into piles based on their first-choice candidate. If your first choice was Ms. Green, your ballot would be placed into Ms. Green's pile. Then the candidate whose pile has the smallest number of ballots is eliminated. The ballots are then redistributed to the other piles according to their second-choice candidate. This process continues until there is only one pile left. The candidate who this pile belongs to is the winner.
