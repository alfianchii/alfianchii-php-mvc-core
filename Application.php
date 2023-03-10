<?php
// The namespace
namespace alfianchii\phpmvc;

use alfianchii\phpmvc\db\Database;

class Application
{
    // Consts event
    const EVENT_BEFORE_REQUEST = "beforeRequest";
    const EVENT_AFTER_REQUEST = "afterRequest";

    // Events
    protected array $eventListeners = [];

    // Properties
    public static string $ROOT_DIR;
    public static Application $app;
    public string $userClass;
    public string $layout = "main";
    public Router $router;
    public Request $request;
    public Response $response;
    public ?Controller $controller = null;
    public Database $db;
    public Session $session;
    public ?UserModel $user;
    public View $view;

    // Constructor (when the class was instaced, run this constructor)
    public function __construct($rootPath, array $config)
    {
        // Fill out the properties
        self::$ROOT_DIR = $rootPath;
        self::$app = $this;
        $this->userClass = $config["userClass"];
        $this->request = new Request();
        $this->response = new Response();
        $this->router = new Router($this->request, $this->response);
        $this->db = new Database($config["db"]);
        $this->session = new Session();
        $this->view = new View();

        /*
            Noting, we should never use any class inside the core which is outside the core.
        */
        // Get the user's session if exists, 
        $primaryValue = $this->session->get("user");
        if ($primaryValue) {
            // Then load the user based on session
            $primaryKey = $this->userClass::primaryKey();
            $this->user = $this->userClass::findOne([$primaryKey => $primaryValue]);
        } else {
            // Otherwise set user to null
            $this->user = null;
        }
    }

    /*
        Methods
    */
    // Running the application
    public function run()
    {
        // Trigger an event
        $this->triggerEvent(self::EVENT_BEFORE_REQUEST);

        // If in "try" occurs an error, just throw the error to "catch"
        try {
            echo $this->router->resolve();
        } catch (\Exception $e) {
            // Set the status code and render view of error
            $this->response->setStatusCode($e->getCode());
            echo $this->view->renderView("_error", [
                "exception" => $e
            ]);
        }
    }

    // Getter and setter of Controller
    public function getController(): \alfianchii\phpmvc\Controller
    {
        return $this->controller;
    }

    public function setController(\alfianchii\phpmvc\Controller $controller): void
    {
        $this->controller = $controller;
    }

    // Save user's login into session (based on id)
    public function login(UserModel $user)
    {
        // Set user
        $this->user = $user;
        // Take the primary key
        $primaryKey = $user->primaryKey();
        // Take the primary's value from user
        $primaryValue = $user->{$primaryKey};
        // Set the session
        $this->session->set("user", $primaryValue);

        return true;
    }

    // User's logout
    public function logout()
    {
        // Set user to null and remove the session of "user"
        $this->user = null;
        $this->session->remove("user");
    }

    // If the user was guest
    public static function isGuest()
    {
        // Whether user was null
        return !self::$app->user;
    }

    // Register an event
    public function on($eventName, $callback)
    {
        // Add the event
        $this->eventListeners[$eventName][] = $callback;
    }

    // Trigger an event
    public function triggerEvent($eventName)
    {
        // Take the callback from $eventListeners for the given $eventName
        $callbacks = $this->eventListeners[$eventName] ?? [];

        // Iterates it, then execute
        foreach ($callbacks as $callback) {
            call_user_func($callback);
        }
    }
}