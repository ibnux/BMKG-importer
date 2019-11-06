## Importer Data Prakiraan Cuaca BMKG

![Cuaca](http://data.bmkg.go.id/assets/img/cuaca.svg)
Script PHP untuk import data prakiraan cuaca dari BMKG, dan ditambahkan ke database MYSQL, sehingga untuk kebutuhan ambil data cuaca bisa langsung query tanpa harus rekues lagi ke server BMKG

Apa yang saya lakukan dengan data ini?

Aplikasi saya bisa mencari wilayah terdekat dari table **t_wilayah**, sehingga cuaca yang ditampilkan sesuai wilayahnya terdekat, di Android saya buat versi SQLITE dan saya query wilayah terdekat dari situ, lalu ambil data cuacanya ke server.

Script ini bisa dijalankan di Browser ataupun di command line, tapi bagusnya di commandline dan gunakan [crontab](https://crontab.guru/#0_3_*_*_*) agar dieksekusi tiap waktu yang ditentukan

Dan ingat, bahwa anda harus memberitahukan jika datanya dari BMKG.

#### Sumber

- [BMKG](http://data.bmkg.go.id/prakiraan-cuaca/) 
- [ICON](http://www.iconarchive.com/tag/weather)
- [Medoo](http://www.iconarchive.com/tag/weather)

Silahkan dimanfatkan untuk keperluan anda

Salam



Ibnu Maksum
@ibnux