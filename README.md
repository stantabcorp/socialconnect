<p align="center"><img src="SocialConnect_Logo.png" width="100"></p>
<h1 align="center">SocialConnect</h1>
The objectives of that project are to bring together most of the third-party login process!
In order to use authentification for users management, you will need a valid SSL certificate

- [x] Facebook
- [x] Twitter
- [x] Google
- [x] Github
- [ ] Discord
- [ ] Spotify
- [ ] Twitch
- [ ] Reddit
- [ ] Steam
- [ ] Dropbox
- [ ] Bitbucket

Please, note that some item of this list may be removed.
If you know more third-party login process, open an issue!

Icon:  
Thibault Junin and [P.J. Onori](https://www.iconfinder.com/icons/118607/share_icon#size=512)

## Add a service
If you want to add a service, create 2 files:
- `<YourService>Service.php` in `core/Services` you can follow the `ExampleService.php` file
- `<YouService>Controller.php` in `core/Controllers/Service` you can copy an existing ServiceController file and simply edit the `__construct` Method

If you want to add configuration element please add them in the right place.

In `core/Controller/User/UserController.php` add this line in the `__construct` method:
```php
$this->service['<youservice>']  = new \SocialConnect\Services\<YourService>Service;
```

In the `index.php` file add the following routes for your service:
In the `/auth` group:
```php
$this->get('/<yourservice>[/]', \SocialConnect\Controllers\Service\<YourService>Controller::class.':getAuthUrl')->setName('<yourservice>.auth');
```

In the `/auth/callback` group:

```php
$this->get('/<yourservice>[/]', \SocialConnect\Controllers\Service\<YourService>Controller::class.':getAuthCallback')->setName('<yourservice>.auth.callback');
```

Then submit a pull request if you want your service to be available for everyone ;)

## License

More infos on https://stantabcorp.com/license
