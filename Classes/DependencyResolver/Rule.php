<?php

/*
 * This file is part of Composer.
 *
 * (c) Nils Adermann <naderman@naderman.de>
 *     Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Composer\DependencyResolver;

/**
 * @author Nils Adermann <naderman@naderman.de>
 */
class Rule
{
    protected $disabled;
    protected $literals;
    protected $type;
    protected $id;
    protected $weak;

    public $watch1;
    public $watch2;

    public $next1;
    public $next2;

    public function __construct(array $literals, $reason, $reasonData)
    {
        // sort all packages ascending by id
        usort($literals, array($this, 'compareLiteralsById'));

        $this->literals = $literals;
        $this->reason = $reason;
        $this->reasonData = $reasonData;

        $this->disabled = false;
        $this->weak = false;

        $this->watch1 = (count($this->literals) > 0) ? $literals[0]->getId() : 0;
        $this->watch2 = (count($this->literals) > 1) ? $literals[1]->getId() : 0;

        $this->type = -1;

        $this->ruleHash = substr(md5(implode(',', array_map(function ($l) {
            return $l->getId();
        }, $this->literals))), 0, 5);
    }

    public function getHash()
    {
        return $this->ruleHash;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * Checks if this rule is equal to another one
     *
     * Ignores whether either of the rules is disabled.
     *
     * @param  Rule $rule The rule to check against
     * @return bool       Whether the rules are equal
     */
    public function equals(Rule $rule)
    {
        if ($this->ruleHash !== $rule->ruleHash) {
            return false;
        }

        if (count($this->literals) != count($rule->literals)) {
            return false;
        }

        for ($i = 0, $n = count($this->literals); $i < $n; $i++) {
            if (!$this->literals[$i]->getId() === $rule->literals[$i]->getId()) {
                return false;
            }
        }

        return true;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function disable()
    {
        $this->disabled = true;
    }

    public function enable()
    {
        $this->disabled = false;
    }

    public function isDisabled()
    {
        return $this->disabled;
    }

    public function isEnabled()
    {
        return !$this->disabled;
    }

    public function isWeak()
    {
        return $this->weak;
    }

    public function setWeak($weak)
    {
        $this->weak = $weak;
    }

    public function getLiterals()
    {
        return $this->literals;
    }

    public function isAssertion()
    {
        return 1 === count($this->literals);
    }

    public function getNext(Literal $literal)
    {
        if ($this->watch1 == $literal->getId()) {
            return $this->next1;
        } else {
            return $this->next2;
        }
    }

    public function getOtherWatch(Literal $literal)
    {
        if ($this->watch1 == $literal->getId()) {
            return $this->watch2;
        } else {
            return $this->watch1;
        }
    }

    /**
     * Formats a rule as a string of the format (Literal1|Literal2|...)
     *
     * @return string
     */
    public function __toString()
    {
        $result = ($this->isDisabled()) ? 'disabled(' : '(';

        foreach ($this->literals as $i => $literal) {
            if ($i != 0) {
                $result .= '|';
            }
            $result .= $literal;
        }

        $result .= ')';

        return $result;
    }

    /**
     * Comparison function for sorting literals by their id
     *
     * @param  Literal $a
     * @param  Literal $b
     * @return int        0 if the literals are equal, 1 if b is larger than a, -1 else
     */
    private function compareLiteralsById(Literal $a, Literal $b)
    {
        if ($a->getId() === $b->getId()) {
            return 0;
        }
        return $a->getId() < $b->getId() ? -1 : 1;
    }
}
