[![Build Status](https://travis-ci.org/dmecke/AchievementBundle.svg)](https://travis-ci.org/dmecke/AchievementBundle)

Installation
============

1. Add the following to your `composer.json` file:

    ```js
    // composer.json
    {
        // ...
        require: {
            // ...
            "cunningsoft/achievement-bundle": "~0.2"
        }
    }
    ```

2. Run `composer update cunningsoft/achievement-bundle` to install the new dependencies.

3. Register the new bundle in your `AppKernel.php`:

    ```php
    <?php
    // in AppKernel::registerBundles()
    $bundles = array(
        // ...
        new Cunningsoft\AchievementBundle\CunningsoftAchievementBundle(),
        // ...
    );
    ```

4. Let your user entity implement the `Cunningsoft\AchievementBundle\Entity\UserInterface`:

    ```php
    // Acme\ProjectBundle\Entity\User.php
    <?php

    namespace Acme\ProjectBundle\Entity;

    use Cunningsoft\AchievementBundle\Entity\UserInterface as AchievementUserInterface;
    use Cunningsoft\AchievementBundle\Entity\Achievement;

    class User implements AchievementUserInterface
    {
        /**
         * @var int
         */
        protected $id;

        /**
         * @var Achievement[]
         *
         * @ORM\OneToMany(targetEntity="Cunningsoft\AchievementBundle\Entity\Achievement", mappedBy="user")
         * @ORM\OrderBy({"insertDate" = "DESC"})
         */
        protected $achievements;

        /**
         * @return int
         */
        public function getId()
        {
            return $this->id;
        }

        /**
         * @return Achievement[]
         */
        public function getAchievements()
        {
            return $this->achievements;
        }
        // ...
    ```

5. Map the interface to your user entity in your `config.yml`:

    ```yaml
    // app/config/config.yml
    // ...
    doctrine:
        orm:
            resolve_target_entities:
                Cunningsoft\AchievementBundle\Entity\UserInterface: Acme\ProjectBundle\Entity\User
    ```

6. Update your database schema:

    ```bash
    $ app/console doctrine:schema:update
    ```

7. Import routes:

    ```yaml
    // app/config/routing.yml
    // ...
    cunningsoft_achievement_bundle:
        resource: "@CunningsoftAchievementBundle/Controller"
        type: annotation
    ```

8. Render the achievement list in your template:

    ```twig
    // src/Acme/ProjectBundle/Resources/views/Default/index.html.twig
    // ...
    {% render(controller('CunningsoftAchievementBundle:Default:achievements', { 'user': app.user } )) %}
    // ...
    ```

9. Render the "achievement unlocked" layer at the bottom of your template:

    ```twig
    // src/Acme/ProjectBundle/Resources/views/Default/index.html.twig
    // ...
    {% render(controller('CunningsoftAchievementBundle:Default:achievementMessages')) %}
    // ...
    ```

10. Import js and css files:

    ```twig
    // app/Resources/views/base.html.twig
    // ...
    {% block javascripts %}
    // ...
    {% javascripts '@CunningsoftAchievementBundle/Resources/public/js/*.js' output='js/achievements.js' %}
        <script type="text/javascript" src="{{ asset_url }}"></script>
    {% endjavascripts %}
    // ...
    {% endblock %}
    // ...
    {% block stylesheets %}
    // ...
    {% stylesheets '@CunningsoftAchievementBundle/Resources/public/css/*.scss' output='css/achievements.css' filter='compass' %}
        <link href="{{ asset_url }}" rel="stylesheet" />
    {% endstylesheets %}
    // ...
    {% endblock %}
    // ...
    ```

11. Create a child bundle which holds all informations about your achievements

    ```
    mkdir src/Acme/AchievementBundle
    ```

    ```php
    // src/Acme/AchievementBundle/AcmeAchievementBundle.php
    <?php

    namespace Acme\AchievementBundle;

    use Symfony\Component\HttpKernel\Bundle\Bundle;

    class AcmeAchievementBundle extends Bundle
    {
        public function getParent()
        {
            return 'CunningsoftAchievementBundle';
        }
    }
    ```

    ```php
    // src/Acme/AchievementBundle/Listener/AchievementListener.php
    <?php

    namespace Acme\AchievementBundle\Listener;

    use Cunningsoft\AchievementBundle\Services\AchievementService;
    use Doctrine\ORM\EntityManager;

    class AchievementListener
    {
        /**
         * @var AchievementService
         */
        private $achievementService;

        /**
         * @var EntityManager
         */
        private $entityManager;

        /**
         * @param AchievementService $achievementService
         * @param EntityManager $entityManager
         */
        public function __construct(AchievementService $achievementService, EntityManager $entityManager)
        {
            $this->achievementService = $achievementService;
            $this->entityManager = $entityManager;
        }
    }
    ```

12. Tell twig where to find your assets

    ```yaml
    // app/config/config.yml
    // ...
    twig:
        globals:
            achievement_bundle_name: acmeachievement
    ```

Usage
=====

For every achievement you want to use, go through these steps. This is an example showing how to give an achievement for posting a comment with at least 100 characters.

1. Add it to your achievement list

    ```yaml
    // app/config/achievements.yml
    // ...
    community: // this is the category name
        comment_100_characters: // this is an unique identifier
            name: Verbosity // this is your custom name, shown to the user
            event: comment_posted // this is the event that is listened for
            class: Acme\AchievementBundle\Listener\AchievementListener // the class you handle all events in
            method: onCommentPosted // the method that is triggered when the event is dispatched
    ```

2. Create an event class as a container for the informations about the event

    ```php
    // src/Acme/ProjectBundle/Event/CommentEvent.php
    <?php
    
    namespace Acme\ProjectBundle\Event;
    
    use Acme\ProjectBundle\Entity\Comment;
    use Symfony\Component\EventDispatcher\Event;
    
    class CommentEvent extends Event
    {
        /**
         * @var Comment
         */
        protected $comment;
    
        public function __construct(Comment $comment)
        {
            $this->comment = $comment;
        }
    
        /**
         * @return Comment
         */
        public function getComment()
        {
            return $this->comment;
        }
    }
    ```

3. Create method to check for the achievement

    ```php
    // src/Acme/AchievementBundle/Listener/AchievementListener.php
    // ...
    public function onCommentPosted(CommentEvent $event)
    {
        if (mb_strlen($event->getComment()->getMessage()) >= 100) {
            $this->achievementService->trigger('community', 'comment_100_characters', $event->getComment()->getAuthor());
        }
    }
    // ...
    ```

3. Create achievement image

    ```
    // src/Acme/AchievementBundle/Resources/public/images/achievements/community/comment_100_characters.png
    ```

4. Add achievement description

    ```yml
    // src/Acme/AchievementBundle/Resources/translations/achievements.en.yml
    description_community_comment_100_characters: Write a comment with at least 100 characters!
    ```

5. Create event

    ```php
    // src/Acme/MainBundle/Controller/Comment.php
    // ...
    public function create()
    {
        // ...
        $this->get('event_dispatcher')->dispatch('comment_posted', new CommentEvent($comment));
    }
    // ...

Now every time a comment is created (in your business logic) an event is triggered. Because of your achievements.yml the achievement system knows to listen for that event and trigger the according method in your AchievementListener. Now the method in your listener can check whether or not the requirements for one or more achievements are met and if so it triggers the booking of the achievement. Of course you may also utilize existing events or use the events for other purposes as well.


Changelog
=========
* 0.2 (master)

* 0.1
First working version.


Notes
=====
Please also visit my Open Source Browsergame Project [Open Soccer Star](https://github.com/dmecke/OpenSoccerStar).

