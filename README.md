# lastlabs-lite
This is very, very, VERY limited version of LastLabs.pl, a website I created and managed from ~2011 to ~2015.
That website contained 4 small apps, Milestones, Artist of the Month, Musicbars and MusicTime, all of them were generating images containing some data or stats based on user's Last.fm profile:
* Milestones was showing selected tracks in order of listening, as in, 1, 1000, 2000... track and so on. Original version was generating BBCode, later versions were based on PNG files with the same data and option to display album cover. Premium version (for those who donated on PayPal) was refreshing data automatically, without the need to regenerate the PNG/BBCode.

![milestones](http://i.imgur.com/xQnQvrI.png)
* Artist of the Month was just an image with name (or logo, if there was one in my database) and photo of the band you listened to the most in the previous month. 

![aotm](http://i.imgur.com/xoJGw3R.png)
* Musicbars generated a userbar, something you would put into your signature years ago, when forums and discussion boards were still popular, with the song you were currently listening to (or the last track you listened to before stopping).

![mbar](http://i.imgur.com/veZTxTl.png)
* MusicTime was showing the time (as opposed to number of tracks listened) spent on listening to music in last 7 days or last 3 months (total listening time or per day).

![mtime](http://i.imgur.com/jm7YPWm.png)

I abandoned the project for a few reasons:
* First and foremost, Last.fm completely changed user profiles and it's no longer possible to add anything to your profile aside from plain text, so there was no way to show these things to everyone there. Obviously, you can put it on your blog or whatever, but how many people have that? And how many people actually care about sharing that somewhere else aside from their Last.fm profile?
* I had a big problem with hosting all those images. Earlier I was using imageshack, which changed a lot a while ago, then I moved to Imgur, but soon after that move they changed their API and I was simply too lazy to fix this, when I finally did that, I'm pretty sure they banned me for some reason.
* Frankly speaking, I stopped using Last.fm at that time because I'm a perfectionist and taking care of metadata of EVERYTHING I listened to was just to difficult for me to handle. I stopped caring about Last.fm and LastLabs is a victim of that, too.
* Almost no free time because of my studies.

But regardless, it was my first really serious project and I'm proud of myself, considering that vast majority of that code was written when I was 17 years old (2011-12). That's why I cleaned it up a little bit and put it here on Github. All of them work properly, though I didn't test it too thoroughly, there are some bugs out there for sure. The code is definitely far from perfect or even good, but there's just no point putting too much work into it, especially considering the changes to Last.fm. If anyone wants to build something based on this code, feel free to do it.
