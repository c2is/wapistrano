<?php

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

use Behat\MinkExtension\Context\MinkContext;

//
// Require 3rd-party libraries here:
//
//   require_once 'PHPUnit/Autoload.php';
//   require_once 'PHPUnit/Framework/Assert/Functions.php';
//

/**
 * Features context.
 */
class FeatureContext extends MinkContext
{
    /**
     * Initializes context.
     * Every scenario gets its own context object.
     *
     * @param array $parameters context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
        //$this->useContext('mink', new MinkContext());
    }

    /**
     * @Given /^I am logged as an admin user "([^"]*)" "([^"]*)"$/
     */
    public function iAmLoggedAsAnAdminUser($userName, $passWord)
    {
        $this->visit("/login");
        $this->fillField("username", $userName);
        $this->fillField("password", $passWord);
        $this->pressButton("login");
    }

    /**
     * @Given /^I click on "([^"]*)" "([^"]*)"$/
     */
    public function iClickOn($selectorType, $selector)
    {


        if ("xpath" == $selectorType) {
            $el = $this->getSession()->getPage()->find(
                'xpath',
                $this->getSession()->getSelectorsHandler()->selectorToXpath('xpath', $selector)
            );
        } else {
            $el = $this->getSession()->getPage()->find($selectorType, $selector);
        }

        if (null == $el) {
            throw new Exception("No element to click on found at ".$selector);
        } else {
            $el->mouseOver();
            $this->mouseOverSelenium($selectorType, $selector, 1);
            $el->click();
        }

    }

    /**
     * @Then /^I wait "([^"]*)"$/
     */
    public function iWait($milliSec)
    {
        $this->getSession()->wait($milliSec);
        usleep($milliSec * 1000);
    }

    protected function mouseOverSelenium($selectorType, $selector, $sleep = 0)
    {
        if ("css" == $selectorType) {
            $selectorType .= " selector";
        }

        // This returns a WebDriver\Session instance.
        $driverSession = $this->getSession()->getDriver()->getWebDriverSession();
        $element = $driverSession->element($selectorType, $selector);
        if (null == $element) {
            throw new Exception("No element to hover found at ".$selector);
        } else {
            $driverSession->moveto(array('element' => $element->getID()));

            // Allow any slow javascript to do its thing.
            if ($sleep) {
                sleep($sleep);
            }
        }
    }
    /**
     * @Then /^I wait for element "([^"]*)" to appear$/
     */
    public function iWaitForElementToAppear($cssSelector)
    {

        $this->getSession()->wait(1000,"$('".$cssSelector."').length > 0");
    }

    /**
     * @Then /^I wait for text "([^"]*)" to appear$/
     */
    public function iWaitForTextToAppear($text)
    {
        $found = false;
        for($i=0; $i<=10; $i++) {
            try {
                $this->assertPageContainsText($text);
                $found = true;
                break;
            }
            catch(Exception $e) {
                $this->getSession()->wait(1000);
                continue;
            }
        }
        if(!$found) {
            $message = sprintf('The text "%s" was not found anywhere in the text of the current page.', $text);
            throw new Exception($message);
        }
    }

    /**
     * @Given /^I fill in "([^"]*)" with$/
     */
    public function iFillInWith($fieldName, PyStringNode $string)
    {
        $this->fillField($fieldName, $string);
    }
}
