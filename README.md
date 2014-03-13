# Latinquiz

A Latin grammar quiz generator.

**Lovingly hand-crafted *just for you* by Michael Baker, 2012**

https://github.com/orochi235/latinquiz

These are quizzes I scripted to help study for an intensive Latin course a couple of years ago. They present conjugation/declension/whatever-it-is-you-do-to-adjectives charts as HTML forms, and evaluate each response as it's entered.

**Most people will want to start with premade quizzes.** These are located in the "premade" directory. These HTML files can be downloaded and redistributed with no external dependencies, and should function in any modern web browser.

Also included are scripts to scrape verb conjugations from verbix.com and generate new quizzes from the results. All I can really say about these is that unlike me, they worked in the summer of 2012. (Actually, I _can_ say a few more things about them, and I'll do so in the "Technical Details" section.)

I've also included the raw text files I entered by hand before I got ambitious/greedy/bored enough to write a scraper. Maybe someone will find a use for them. Probably not. But maybe.

## Taking the Quizzes

Simply click any text field on the chart to enter your answer. Press 'enter' or 'tab' to submit your answer and move to the next field. Click the 'shuffle' checkbox at the top of the page for a more challenging experience.

As you submit answers, fields are color-coded by result:
* Green: you got it right
* Red: you got it wrong
* Blue: you punked out and asked the computer for the answer

If you get a field wrong or leave it blank, you can return to it and submit a new answer as many times as you like. Once a field contains a correct answer, it will lock. (You can print completed quizzes for a handy study guide.) 

Refresh the page or click 'clear all' to unlock and clear all fields.

Click 'show answers' to fill in all blank and incorrect fields. You can distinguish fields you gave up on from fields you answered correctly by their background color.

## Technical Details

They're written in PHP (sorry) and designed to output a self-contained HTML "applet". It uses the DOM API's instead of jQuery because I don't think I knew what jQuery was at the time. I ran them on an Apache 2.0 server with either PHP 4 or 5. Install a web server and get PHP running on it, place this project on the server, and then access the appropriate PHP script via a web browser. Save what's sent to your browser as an HTML file, and distribute as needed.

Oh, and you might need some weird internationalization library to run the Unicode-normalizing part of the scraper. I don't really remember. You're smart; you'll figure it out.

I take no responsibility for the code quality of these scripts; they were written quickly, not well. It should be possible to modify them for other languages, curricula (see? they work!) and parts of speech without too much effort. But just to be clear, we're talking about *your* effort, not mine. :)

## License

Do whatever the fuck you want with it. (Or don't.)
