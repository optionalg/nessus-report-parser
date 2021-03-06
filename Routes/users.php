<?php
/**
 * nessus-report-parser -- users.php
 * User: Simon Beattie
 * Date: 11/06/2014
 * Time: 16:46
 */


$app->hook('slim.before.dispatch', function() use($app){

    $currentRoute = $app->router()->getCurrentRoute()->getName();

    if(!array_key_exists('userId', $_SESSION) && !in_array($currentRoute, ['loginGet', 'loginPost']))
    {
        $app->redirect('/login');
        return;
    }

    if(array_key_exists('userId', $_SESSION))
    {
        $app->userId = $_SESSION['userId'];
    }

});


$app->post('/admin/adduser', function() use($app, $pdo)
{
    $users = new \Library\Users($pdo);

    //Sanitise
    $email = strip_tags($app->request()->post('email'));
    $name = strip_tags($app->request()->post('name'));
    $password = $app->request()->post('password');
    $priv = strip_tags($app->request()->post('priv'));

    $result = $users->createUser($name, $email, $password, $priv);
    $app->redirect('/admin/adduser?result='.$result);
});

$app->get('/admin/adduser', function() use($app)
{
    $app->render('users/adduser.phtml', array('app' => $app));
});


$app->get('/admin/severity', function() use($app, $pdo)
{
    $users = new \Library\Users($pdo);

    $userDetails = $users->getUserDetails($_SESSION['email']);
    $app->render('users/severity.phtml', array( 'userDetails' => $userDetails, 'app' => $app));
});

$app->post('/admin/severity', function() use ($app, $pdo)
{
    $severity = strip_tags($app->request()->post('severity'));

    $users = new \Library\Users($pdo);

    $result = $users->setSeverity($_SESSION['userId'], $severity);

    $app->redirect('/admin/severity?result='.$result);
});

$app->get('/admin/changepass', function() use($app)
{
    $app->render('users/changePass.phtml', array('app' => $app));
});

$app->post('/admin/changepass', function() use($app, $pdo)
{

    $password = $app->request()->post('oldpass');
    $newPass = $app->request()->post('newpass');
    $repeatPass = $app->request()->post('repeat');

    $users = new \Library\Users($pdo);

    $result = $users->changeUserPass($_SESSION['email'], $_SESSION['userId'],$password, $newPass, $repeatPass);

    $app->redirect('/admin/changepass?result='.$result);
});


$app->post('/login', function() use($app, $pdo)
{
    $email = strip_tags($app->request()->post('username'));
    $password = hash('sha512', $app->request()->post('password'));

    $users = new \Library\Users($pdo);

    $userId = $users->checkUser($email, $password);
    if($userId)
    {
        $_SESSION['userId'] = $userId['id'];
        $_SESSION['email'] = $userId['email'];
        $_SESSION['name'] = $userId['name'];

        $app->redirect('/');
        return;
    }

    $app->redirect('/login?loggedIn=true');

})->setName('loginPost');


$app->get('/logout', function() use($app)
{
    session_destroy();
    $app->redirect('/');
});

$app->get('/login', function() use($app)
{
    $app->render('users/login.phtml', array('app' => $app));
})->setName('loginGet');












