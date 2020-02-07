<?php

namespace App;

use Throwable;

class GildedRoseTest extends \PHPUnit\Framework\TestCase {
    const REGULAR_ITEM_NAME = "Regular Item";
    const IRRELEVANT_SELL_BY_DATE = 10;
    const DEFAULT_INITIAL_QUALITY = 10;
    const DEFAULT_QUALITY_AFTER_ONE_DAY = 9;
    const MAXIMUM_ITEM_QUALITY = 50;

    /**
     * @test
     */
    public function itShouldDecreaseQualityAtTheEndOfTheDayForRegularItems()
    {
        $item = $this->generateRegularItem();
        $gildedRose = new GildedRose([$item]);

        $gildedRose->updateQuality();

        $this->assertEquals(self::DEFAULT_QUALITY_AFTER_ONE_DAY, $item->quality);
    }

    /**
     * @test
     */
    public function itShouldNeverHaveANegativeQualityRegularItems() {
        $item = $this->generateRegularItemWithZeroQuality();
        $gildedRose = new GildedRose([$item]);

        $gildedRose->updateQuality();

        $this->assertEquals(0, $item->quality);
    }

    /**
     * @test
     */
    public function itShouldIncreaseInQualityIfAgedBrieItem() {
        $item = $this->generateBrieItemWithZeroQuality();
        $gildedRose = new GildedRose([$item]);

        $gildedRose->updateQuality();

        $this->assertEquals(1, $item->quality);
    }


    /**
     * @test
     */
    public function itShouldNeverIncreaseInQualityOverFiftyIfAgedBrieItem() {
        $item = $this->generateBrieItemWithFiftyQuality();
        $gildedRose = new GildedRose([$item]);

        $gildedRose->updateQuality();

        $this->assertEquals(self::MAXIMUM_ITEM_QUALITY, $item->quality);
    }

    /**
     * @test
     */
    public function itShouldAgeAllItemsAccordingToFullRulesSet()
    {
        $outputFileName = __DIR__ . '/result.txt';
        $output = fopen($outputFileName, 'wb');

        fputs($output, "OMGHAI!\n");

        $items = array(
            new Item('+5 Dexterity Vest', 10, 20),
            new Item('Aged Brie', 2, 0),
            new Item('Elixir of the Mongoose', 5, 7),
            new Item('Sulfuras, Hand of Ragnaros', 0, 80),
            new Item('Sulfuras, Hand of Ragnaros', -1, 80),
            new Item('Backstage passes to a TAFKAL80ETC concert', 15, 20),
            new Item('Backstage passes to a TAFKAL80ETC concert', 10, 49),
            new Item('Backstage passes to a TAFKAL80ETC concert', 5, 49),
            // this conjured item does not work properly yet
            new Item('Conjured Mana Cake', 3, 6)
        );

        $app = new GildedRose($items);

        $days = 2;
        for ($i = 0; $i < $days; $i++) {
            fputs($output, "-------- day $i --------\n");
            fputs($output, "name, sellIn, quality\n");
            foreach ($items as $item) {
                fputs($output, $item . PHP_EOL);
            }
            fputs($output, PHP_EOL);


            $app->updateQuality();
        }

        fclose($output);

        $this->assertTrue($this->files_are_equal($outputFileName, __DIR__ . "/goldenmaster.txt"));


    }

    private function files_are_equal($a, $b)
    {
        // Check if filesize is different
        if (filesize($a) !== filesize($b))
            return false;
        // Check if content is different
        $ah = fopen($a, 'rb');
        $bh = fopen($b, 'rb');
        $result = true;
        while (!feof($ah)) {
            if (fread($ah, 8192) != fread($bh, 8192)) {
                $result = false;
                break;
            }
        }
        fclose($ah);
        fclose($bh);
        return $result;
    }

    /**
     * @return Item
     */
    private function generateRegularItem(): Item
    {
        $item = new Item(self::REGULAR_ITEM_NAME,
            self::IRRELEVANT_SELL_BY_DATE,
            self::DEFAULT_INITIAL_QUALITY);
        return $item;
    }

    /**
     * @return Item
     */
    private function generateRegularItemWithZeroQuality(): Item
    {
        $item = $this->generateRegularItem();
        $item->quality = 0;
        return $item;
    }



    /**
     * @return Item
     */
    private function generateBrieItemWithZeroQuality(): Item
    {
        $item = $this->generateRegularItemWithZeroQuality();
        $item->name = GildedRose::ITEM_NAME_AGED_BRIE;
        return $item;
    }

    /**
     * @return Item
     */
    private function generateBrieItemWithFiftyQuality(): Item
    {
        $brieItem = $this->generateBrieItemWithZeroQuality();
        $brieItem->quality = 50;
        return $brieItem;
    }


}
