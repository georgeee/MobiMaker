Mobi maker
===================
Template-code for creating mobi files (e.g. to put it on your Kindle) from html pages.
It's written for parsing wiki-summaries from neerc.ifmo.ru/wiki.


Structure is such:
  -  new_project.bash - creates the folder with name as argument 1 and copies blank settings.ini to new folder
  -  directory_parser.php - parses directory index of articles, saves it to directory_saved.php in current folder
  -  doc_downloader.php - downloads pages and images
  -  opf_generator.php - generates .opf, .ncx and toc.html files
  -  kindlegen/kindlegen - [Kindlegen](http://www.amazon.com/gp/feature.html?ie=UTF8&docId=1000765211) by Amazon


Sequence to get your mobi:
```bash
./new_project.bash prj_name
cd prj_name
nano settings.ini #fill in the settings
php ../directory_parser.php
nano directory_saved.php #Edit it, if you need to alter the structure
php ../doc_downloader.php
php ../opf_generator.php
../kindlegen/kindlegen book.opf
```

It will result in book.mobi in prj_name folder


Note if you're going to parse neerc's wiki:
В параметр url в settings.ini вбивается ссылка версии для печати, пример: [Конспекты лекций по математическому анализу, 1 курс (Н.Ю. Додонов)](http://neerc.ifmo.ru/wiki/index.php?title=%D0%9C%D0%B0%D1%82%D0%B5%D0%BC%D0%B0%D1%82%D0%B8%D1%87%D0%B5%D1%81%D0%BA%D0%B8%D0%B9_%D0%B0%D0%BD%D0%B0%D0%BB%D0%B8%D0%B7_1_%D0%BA%D1%83%D1%80%D1%81&printable=yes)