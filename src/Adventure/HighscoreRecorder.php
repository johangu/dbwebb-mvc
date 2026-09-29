<?php

namespace App\Adventure;

use App\Entity\Highscore;
use DateTimeImmutable;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;

/**
 * Puts won games on the highscore list.
 *
 * @author  jogm23
 */
class HighscoreRecorder
{
    private ObjectManager $manager;

    /**
     * Constructor
     *
     * @param ManagerRegistry $doctrine The registry to get the entity manager from
     */
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->manager = $doctrine->getManager();
    }

    /**
     * Save a won game on the highscore list.
     *
     * A game is only saved once, and only if it has been won.
     *
     * @param GameSession $game The player's game session
     *
     * @return bool True if the game was saved, false otherwise
     */
    public function record(GameSession $game): bool
    {
        if (!$game->hasWon() || $game->isRecorded()) {
            return false;
        }

        $highscore = new Highscore();
        $highscore->setName($game->getPlayerName());
        $highscore->setMoves($game->getMoves());
        $highscore->setCreatedAt(new DateTimeImmutable());

        $this->manager->persist($highscore);
        $this->manager->flush();
        $game->markRecorded();

        return true;
    }
}
