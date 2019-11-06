--
-- Database: `bmkg_cuaca`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `t_cuaca`
--

CREATE TABLE `t_cuaca` (
  `idWilayah` int(11) NOT NULL,
  `jamCuaca` datetime NOT NULL,
  `kodeCuaca` int(11) NOT NULL,
  `cuaca` varchar(16) NOT NULL,
  `humidity` int(3) NOT NULL,
  `tempC` int(11) NOT NULL COMMENT 'Temperatur Celcius',
  `tempF` int(11) NOT NULL COMMENT 'Temperatur Fahrenheit'
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Struktur dari tabel `t_wilayah`
--

CREATE TABLE `t_wilayah` (
  `id` int(11) NOT NULL,
  `propinsi` varchar(32) NOT NULL,
  `kota` varchar(32) NOT NULL,
  `kecamatan` varchar(32) NOT NULL,
  `lat` decimal(8,6) NOT NULL,
  `lon` decimal(9,6) NOT NULL
) ENGINE=InnoDB;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `t_cuaca`
--
ALTER TABLE `t_cuaca`
  ADD PRIMARY KEY (`idWilayah`,`jamCuaca`),
  ADD KEY `jamCuaca` (`jamCuaca`),
  ADD KEY `idWilayah` (`idWilayah`);

--
-- Indeks untuk tabel `t_wilayah`
--
ALTER TABLE `t_wilayah`
  ADD PRIMARY KEY (`id`);
COMMIT;
