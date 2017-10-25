<?php
namespace tests;
use tests\ApiTester;
use Fixtures\UserFixturesFinal as User;
/**
 * @IgnoreAnnotation("dataprovider")
 */
class PostUserConfirmAgreementsCest
{
    const API_URL = 'v1/';
    public $userPro;
    public $userInvestor;
    public $token;
    public $code;
    public $id_pro;
    public $id_investor;
  
    protected function CreatePro(ApiTester $I, User $user)
    {
        $this->userPro = $user->get('testpro_user');
        $I->sendPOST(self::API_URL . 'user', [
                'username' => $this->userPro['username'],
                'email' => $this->userPro['email'],
                'phoneNumber' => $this->userPro['phoneNumber'],
                'password' => $this->userPro['password'],
                'firstName' => $this->userPro['firstName'],
                'lastName' => $this->userPro['lastName'],
                'avatarUrl' => $this->userPro['avatarUrl']
        ]);
        $this->token = $I->grabDataFromResponseByJsonPath('$.result.token');
        $this->token = $this->token[0];
        $I->amBearerAuthenticated($this->token);        
        $this->code = $I->grabFromDatabase('user_confirmation_email', 'code', array('email' => $this->userPro['email']));
        $I->sendPOST(self::API_URL . 'user/confirm-email', [
            "code" => $this->code
        ]);
        $this->token = $I->grabDataFromResponseByJsonPath('$.result.token');
        $this->token = $this->token[0];
        $I->amBearerAuthenticated($this->token);
        $this->code = $I->grabFromDatabase('user_confirmation_phone', 'code', array('phone_number' => $this->userPro['phoneNumber']));
        $I->sendPOST(self::API_URL . 'user/confirm-phone', [
            "code" => $this->code
        ]);
        $this->id_pro = $I->grabFromDatabase('user', 'id', array('username' => $this->userPro['username']));
        //codecept_debug($this->id_pro);        
    }  
        protected function CreateInvestor(ApiTester $I, User $user)
    {
        $this->userInvestor = $user->get('testinvestor_user');
        $I->sendPOST(self::API_URL . 'user', [
                'username' => $this->userInvestor['username'],
                'email' => $this->userInvestor['email'],
                'phoneNumber' => $this->userInvestor['phoneNumber'],
                'password' => $this->userInvestor['password'],
                'firstName' => $this->userInvestor['firstName'],
                'lastName' => $this->userInvestor['lastName'],
                'avatarUrl' => $this->userInvestor['avatarUrl']
        ]);
        $this->token = $I->grabDataFromResponseByJsonPath('$.result.token');
        $this->token = $this->token[0];
        $I->amBearerAuthenticated($this->token);        
        $this->code = $I->grabFromDatabase('user_confirmation_email', 'code', array('email' => $this->userInvestor['email']));
        $I->sendPOST(self::API_URL . 'user/confirm-email', [
            "code" => $this->code
        ]);
        $this->token = $I->grabDataFromResponseByJsonPath('$.result.token');
        $this->token = $this->token[0];
        $I->amBearerAuthenticated($this->token);
        $this->code = $I->grabFromDatabase('user_confirmation_phone', 'code', array('phone_number' => $this->userInvestor['phoneNumber']));
        $I->sendPOST(self::API_URL . 'user/confirm-phone', [
            "code" => $this->code
        ]);
        $this->id_investor = $I->grabFromDatabase('user', 'id', array('username' => $this->userInvestor['username']));
        //codecept_debug($this->id_investor);        
    }  
    public function _after(ApiTester $I, User $user)
      {  
        $this->userPro = $user->get('testpro_user');
        $I->deleteFromDatabase('user_broker_agreement', ['user_id' => $this->id_pro]);
        $I->wantTo('Delete Pro user');
        $I->deleteFromDatabase('user', ['email' => $this->userPro['email']]);
        $I->dontSeeInDatabase('user', array('email' => $this->userPro['email']));
        $this->userInvestor = $user->get('testinvestor_user');
        $I->deleteFromDatabase('user_broker_agreement', ['user_id' => $this->id_investor]);        
        $I->wantTo('Delete Investor user');
        $I->deleteFromDatabase('user', ['email' => $this->userInvestor['email']]);
        $I->dontSeeInDatabase('user', array('email' => $this->userInvestor['email']));
      }
      public function _failed(ApiTester $I, User $user)
      {  
        $this->userPro = $user->get('testpro_user');
        $I->deleteFromDatabase('user_broker_agreement', ['user_id' => $this->id_pro]);
        $I->wantTo('Delete Pro user');
        $I->deleteFromDatabase('user', ['email' => $this->userPro['email']]);
        $I->dontSeeInDatabase('user', array('email' => $this->userPro['email']));
        $this->userInvestor = $user->get('testinvestor_user');
        $I->deleteFromDatabase('user_broker_agreement', ['user_id' => $this->id_investor]);        
        $I->wantTo('Delete Investor user');
        $I->deleteFromDatabase('user', ['email' => $this->userInvestor['email']]);
        $I->dontSeeInDatabase('user', array('email' => $this->userInvestor['email']));
      }
    /**
     * General function to post confirm agreement 
     * @param \tests\ApiTester $I
     * @param \Codeception\Example $example
     */
    private function PostConfirmAgreement(ApiTester $I, \Codeception\Example $example)
    {
        $I->wantTo('Confirm Agreement');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Accept', 'application/json');
        $I->sendPOST(self::API_URL . 'user/confirm-agreements', [
            'brokerFirst' => $example['data']['brokerFirst'],
            'brokerSecond'=> $example['data']['brokerSecond'],
            'brokerThird' => $example['data']['brokerThird']
        ]);
        $code = $I->grabDataFromResponseByJsonPath('$.code');
        $I->{"assertResult".$code[0]}($example['result']);        
    }
    /**
     * Success post confirm agreement as Pro
     * @before CreatePro
     * @dataprovider dataProviderAuthorizeUser
     * @param \tests\ApiTester $I
     * @param \Codeception\Example $example
     * @param User $user
     */
    public function testPostConfirmAgreementWithRegisteredPro(ApiTester $I, \Codeception\Example $example, User $user) 
    {
        $this->userPro = $user->get('testpro_user');
        $I->amFormAuthenticated($this->userPro);
        $this->PostConfirmAgreement($I, $example);  
    }
     /**
     * Success post confirm agreement as Investor
     * @before CreateInvestor
     * @dataprovider dataProviderAuthorizeUser
     * @param \tests\ApiTester $I
     * @param \Codeception\Example $example
     * @param User $user
     */
    public function testPostConfirmAgreementWithRegisteredInvestor(ApiTester $I, \Codeception\Example $example, User $user) 
    {
        $this->userInvestor = $user->get('testinvestor_user');
        $I->amFormAuthenticated($this->userInvestor);
        $this->PostConfirmAgreement($I, $example);  
    }
    /**
     * Get user profile, 401 error - unauthorize user
     * @dataprovider dataProviderUnauthorizeUser
     * @param \tests\ApiTester $I
     * @param \Codeception\Example $example
     */
    public function testPostConfirmAgreementWithoutLogin(ApiTester $I, \Codeception\Example $example)
    {
        $this->PostConfirmAgreement($I, $example);
    }
    /**
     * Add dataProvider with success data
     * @return type
     */
    private function dataProviderAuthorizeUser()
    {
        return include codecept_data_dir('variations/user/PostUserConfirmAgreements') . '/post_user_confirm_agreements.php';
    }     
    /**
     * Add dataProvider with error data
     * @return type
     */
    private function dataProviderUnauthorizeUser()
    {
        return include codecept_data_dir('variations/user/PostUserConfirmAgreements') . '/post_user_confirm_agreements_unauthorize.php';
    }
}
