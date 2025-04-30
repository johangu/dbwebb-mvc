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

    public function testJsonSerialize(): void
    {
        $player = new Player("Test Testsson");

        $json = json_encode($player);
        $expectedJson =
            json_encode([
                'hand' => [
                    'cards' => []
                ],
                'name' => 'Test Testsson',
            ]);
        $this->assertNotFalse($json);
        $this->assertNotFalse($expectedJson);
        $this->assertJson($json);
        $this->assertJson($expectedJson);
        $this->assertJsonStringEqualsJsonString($expectedJson, $json);
    }
}
