<?php

use Yuloh\PatternRecognition\Matcher;

class PatternRecognitionTest extends \PHPUnit_Framework_TestCase
{
    public function testBasicMatching()
    {
        $pm = (new Matcher())
            ->add(['a' => 1], 'A')
            ->add(['b' => 2], 'B');

        $this->assertSame('A', $pm->find(['a' => 1]));
        $this->assertSame(null, $pm->find(['a' => 2]));
        $this->assertSame('A', $pm->find(['a' => 1, 'b' => 1]));
        $this->assertSame('B', $pm->find(['b' => 2, 'c' => 3]));
    }

    public function testMatchIsNotFoundWhenNotAllKeysMatch()
    {
        $pm = (new Matcher())
            ->add(['x' => 1], 'A')
            ->add(['x' => 1, 'y' => 1], 'B');

        $this->assertSame(null, $pm->find(['y' => 1]));
    }

    public function testMatchingWhenBestMatchHasLessPairs()
    {
        $pm = (new Matcher())
            ->add(['a' => 1, 'x' => 10, 'y' => 11, 'z' => 12], 'A')
            ->add(['a' => 1, 'b' => 2], 'B');

        $this->assertSame('B', $pm->find(['a' => 1, 'b' => 2]));
    }

    public function testMoreSpecificBeatsLessSpecific()
    {
        $pm = (new Matcher())
            ->add(['a' => 0], 'A')
            ->add(['b' => 1], 'B')
            ->add(['c' => 2], 'C')
            ->add(['a' => 0, 'b' => 1], 'AB');

        $this->assertSame('AB', $pm->find(['a' => 0, 'b' => 1]));
    }

    public function testKeysAreMatchedAlphabetically()
    {
        $pm = (new Matcher())
            ->add(['a' => 0], 'A')
            ->add(['b' => 1], 'B')
            ->add(['c' => 2], 'C')
            ->add(['a' => 0, 'b' => 1], 'AB');

        $this->assertSame('A', $pm->find(['c' => 2, 'a' => 0]));
        $this->assertSame('B', $pm->find(['b' => 1, 'c' => 2]));
    }

    public function testRootMatch()
    {
        $pm = (new Matcher())
            ->add([], 'R');

        $this->assertSame('R', $pm->find([]));
        $this->assertSame('R', $pm->find(['x' => 1]));
    }

    public function testRootMatchIsLessSpecific()
    {
        $pm = (new Matcher())
            ->add([], 'R')
            ->add(['a' => 1], 'A');

        $this->assertSame('A', $pm->find(['a' => 1]));
    }


    public function testBasicGlobMatching()
    {
        $pm = (new Matcher())
            ->add(['a' => 0], 'A')
            ->add(['a' => '*'], 'AA')
            ->add(['b' => 1, 'c' => 'x*y'], 'BC')
            ->add(['c' => 1, 'd' => 'x?z'], 'CD');

        $this->assertSame('A', $pm->find(['a' => 0]));
        $this->assertSame('AA', $pm->find(['a' => 1]));
        $this->assertSame('BC', $pm->find(['b' => 1, 'c' => 'xhy']));
        $this->assertSame(null, $pm->find(['d' => 'xyyz']));
        $this->assertSame('CD', $pm->find(['c' => 1, 'd' => 'xyz']));
    }

    public function testGlobMatchIsLessSpecificThanExact()
    {
        $pm = (new Matcher())
            ->add(['a' => 1], 'A')
            ->add(['a' => '*', 'b' =>'*'], 'AB');

        $this->assertSame('A', $pm->find(['a' => 1, 'b' => 2]));
    }

    public function testRemove()
    {
       $pm = (new Matcher())
            ->add(['a' => 1], 'A')
            ->remove(['a' => 1]);

        $this->assertNull($pm->find(['a' => 1]));
    }

    public function testJsonSerialize()
    {
        $pm = (new Matcher())
            ->add(['a' => 1], 'A')
            ->add(['b' => 2], 'B');

        $serialized = $pm->jsonSerialize();

        $expected = [
            ['match' => ['a' => 1], 'data' => 'A'],
            ['match' => ['b' => 2], 'data' => 'B']
        ];

        $this->assertSame($expected, $serialized);
    }
}
