<?php

namespace App\Tests\TwentyOne;

use PHPUnit\Framework\TestCase;
use App\TwentyOne\Player;

class TestPlayer extends TestCase
{
    public function testGetName(): void
    {
        $player = new Player("Test Testsson");
        $this->assertEquals("Test Testsson", $player->getName());
    }

    public function testGetHand(): void
    {
        $player = new Player("Test Testsson");
        $hand = $player->getHand();
        $this->assertEquals(0, count($hand->getCards()));
    }

    public function testJsonSerialization(): void
    {
        $player = new Player("Test Testsson");
        $json = json_encode($player);
        $this->assertJsonStringEqualsJsonString(
            '{"name":"Test Testsson","hand":{ "cards": []}}',
            $json
        );
    }
}
