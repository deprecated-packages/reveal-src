<?php

declare (strict_types=1);
use Latte\Runtime as LR;
/** DummyTemplateClass */
final class DummyTemplateClass extends \Latte\Runtime\Template
{
    public function main() : array
    {
        \extract($this->params);
        /** @var Reveal\LattePHPStanCompiler\Tests\LatteToPhpCompiler\Source\Modules\FooModule\FirstFooPresenter $actualClass */
        echo '<a href="';
        /** line in latte file: 1 */
        /** @var Reveal\LattePHPStanCompiler\Tests\LatteToPhpCompiler\Source\Modules\FooModule\FirstFooPresenter $fooFirstFooPresenter */
        $fooFirstFooPresenter->renderDefault();
        echo '">Only action</a>
<a href="';
        /** line in latte file: 2 */
        /** @var Reveal\LattePHPStanCompiler\Tests\LatteToPhpCompiler\Source\Modules\FooModule\FirstFooPresenter $fooFirstFooPresenter */
        $fooFirstFooPresenter->renderDefault();
        echo '">Same presenter and action</a>
<a href="';
        /** line in latte file: 3 */
        /** @var Reveal\LattePHPStanCompiler\Tests\LatteToPhpCompiler\Source\Modules\FooModule\FirstFooPresenter $fooFirstFooPresenter */
        $fooFirstFooPresenter->renderDefault();
        echo '">Same presenter and action with module</a>
<a href="';
        /** line in latte file: 4 */
        /** @var Reveal\LattePHPStanCompiler\Tests\LatteToPhpCompiler\Source\Modules\FooModule\SecondFooPresenter $fooSecondFooPresenter */
        $fooSecondFooPresenter->actionDefault();
        echo '">Another presenter from same module</a>
<a href="';
        /** line in latte file: 5 */
        /** @var Reveal\LattePHPStanCompiler\Tests\LatteToPhpCompiler\Source\Modules\FooModule\SecondFooPresenter $fooSecondFooPresenter */
        $fooSecondFooPresenter->actionDefault();
        echo '">Another presenter from same module with module</a>
<a href="';
        /** line in latte file: 6 */
        /** @var Reveal\LattePHPStanCompiler\Tests\LatteToPhpCompiler\Source\Modules\BarModule\FirstBarPresenter $barFirstBarPresenter */
        $barFirstBarPresenter->actionDefault();
        $barFirstBarPresenter->renderDefault();
        echo '">Another presenter from another module</a>
<a href="';
        /** line in latte file: 7 */
        /** @var Reveal\LattePHPStanCompiler\Tests\LatteToPhpCompiler\Source\Modules\BarModule\SecondBarPresenter $barSecondBarPresenter */
        $barSecondBarPresenter->renderDefault();
        echo '">Second presenter from another module</a>
';
        return \get_defined_vars();
    }
    public function prepare() : void
    {
        \extract($this->params);
        /** @var Reveal\LattePHPStanCompiler\Tests\LatteToPhpCompiler\Source\Modules\FooModule\FirstFooPresenter $actualClass */
        \Nette\Bridges\ApplicationLatte\UIRuntime::initialize($this, $this->parentName, $this->blocks);
    }
}
