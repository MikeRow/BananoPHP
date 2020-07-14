<?php
// automatically generated by the FlatBuffers compiler, do not modify

namespace MikeRow\NanoPHP\NanoApi;

use \Google\FlatBuffers\Struct;
use \Google\FlatBuffers\Table;
use \Google\FlatBuffers\ByteBuffer;
use \Google\FlatBuffers\FlatBufferBuilder;

class AccountWeightResponse extends Table
{
    /**
     * @param ByteBuffer $bb
     * @return AccountWeightResponse
     */
    public static function getRootAsAccountWeightResponse(ByteBuffer $bb)
    {
        $obj = new AccountWeightResponse();
        return ($obj->init($bb->getInt($bb->getPosition()) + $bb->getPosition(), $bb));
    }

    /**
     * @param int $_i offset
     * @param ByteBuffer $_bb
     * @return AccountWeightResponse
     **/
    public function init($_i, ByteBuffer $_bb)
    {
        $this->bb_pos = $_i;
        $this->bb = $_bb;
        return $this;
    }

    public function getVotingWeight()
    {
        $o = $this->__offset(4);
        return $o != 0 ? $this->__string($o + $this->bb_pos) : null;
    }

    /**
     * @param FlatBufferBuilder $builder
     * @return void
     */
    public static function startAccountWeightResponse(FlatBufferBuilder $builder)
    {
        $builder->StartObject(1);
    }

    /**
     * @param FlatBufferBuilder $builder
     * @return AccountWeightResponse
     */
    public static function createAccountWeightResponse(FlatBufferBuilder $builder, $voting_weight)
    {
        $builder->startObject(1);
        self::addVotingWeight($builder, $voting_weight);
        $o = $builder->endObject();
        $builder->required($o, 4);  // voting_weight
        return $o;
    }

    /**
     * @param FlatBufferBuilder $builder
     * @param StringOffset
     * @return void
     */
    public static function addVotingWeight(FlatBufferBuilder $builder, $votingWeight)
    {
        $builder->addOffsetX(0, $votingWeight, 0);
    }

    /**
     * @param FlatBufferBuilder $builder
     * @return int table offset
     */
    public static function endAccountWeightResponse(FlatBufferBuilder $builder)
    {
        $o = $builder->endObject();
        $builder->required($o, 4);  // voting_weight
        return $o;
    }
}
