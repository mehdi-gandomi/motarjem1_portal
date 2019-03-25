-- phpMyAdmin SQL Dump
-- version 4.6.6deb5
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 23, 2019 at 07:41 PM
-- Server version: 5.7.25-0ubuntu0.18.04.2
-- PHP Version: 7.2.15-0ubuntu0.18.04.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `motarjem1`
--

-- --------------------------------------------------------

--
-- Table structure for table `forgot_password`
--

CREATE TABLE `forgot_password` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_type` tinyint(1) NOT NULL,
  `token` varchar(40) NOT NULL,
  `expire_date` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `forgot_password`
--

INSERT INTO `forgot_password` (`id`, `user_id`, `user_type`, `token`, `expire_date`) VALUES
(4, 1, 2, '24589524305f9484df6b66445c29a26ad966b801', '1550073592'),
(6, 1, 2, '94f657cdf4d4beb840c324d8f1c67a47a5bb189f', '1550084066');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notif_id` int(11) NOT NULL,
  `title` varchar(50) DEFAULT NULL,
  `body` text,
  `importance` tinyint(1) NOT NULL DEFAULT '3',
  `attach_files` varchar(250) DEFAULT NULL,
  `sent_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `sent_date_persian` varchar(16) DEFAULT NULL,
  `notif_type` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notif_id`, `title`, `body`, `importance`, `attach_files`, `sent_date`, `sent_date_persian`, `notif_type`) VALUES
(1, 'تست میشه', 'ودذردنثتبرنثبترد', 1, NULL, '2019-03-07 09:50:42', NULL, 1),
(2, 'تست عمومی', 'رسمنرذسنردبذرنبدربدر', 1, 'test.jpg', '2019-03-07 13:33:06', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `notif_translator`
--

CREATE TABLE `notif_translator` (
  `translator_id` int(11) DEFAULT NULL,
  `notif_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `notif_translator`
--

INSERT INTO `notif_translator` (`translator_id`, `notif_id`) VALUES
(1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `order_number` varchar(8) NOT NULL,
  `orderer_id` int(11) NOT NULL,
  `word_numbers` varchar(10) NOT NULL,
  `translation_quality` tinyint(1) NOT NULL,
  `translation_lang` tinyint(1) NOT NULL,
  `translation_kind` tinyint(1) NOT NULL,
  `delivery_type` tinyint(1) NOT NULL,
  `delivery_days` varchar(3) NOT NULL,
  `order_files` varchar(250) DEFAULT NULL,
  `description` text,
  `order_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `order_date_persian` varchar(16) NOT NULL,
  `field_of_study` varchar(3) DEFAULT NULL,
  `discount_code` varchar(10) DEFAULT NULL,
  `order_price` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `order_number`, `orderer_id`, `word_numbers`, `translation_quality`, `translation_lang`, `translation_kind`, `delivery_type`, `delivery_days`, `order_files`, `description`, `order_date`, `order_date_persian`, `field_of_study`, `discount_code`, `order_price`) VALUES
(21, 'f37a84e3', 2, '250', 5, 1, 1, 1, '1', '', '', '2019-03-02 17:30:26', '1397/12/11 21:00', '0', '', '5000'),
(22, 'f37a84c4', 2, '250', 5, 1, 1, 1, '1', '', '', '2019-03-02 17:30:26', '1397/12/11 21:00', '0', '', '5000'),
(23, 'f37b54c4', 2, '250', 5, 1, 1, 1, '1', '3dba7368848bd09a.jpg', '', '2019-03-02 17:30:26', '1397/12/11 21:00', '0', '', '5000');

-- --------------------------------------------------------

--
-- Table structure for table `order_logs`
--

CREATE TABLE `order_logs` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `translator_id` int(11) DEFAULT '0',
  `transaction_code` varchar(50) DEFAULT '0',
  `is_accepted` tinyint(1) DEFAULT '0',
  `accept_date` varchar(20) DEFAULT NULL,
  `accept_date_persian` varchar(16) DEFAULT NULL,
  `order_step` tinyint(1) NOT NULL DEFAULT '0',
  `is_done` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `order_logs`
--

INSERT INTO `order_logs` (`id`, `order_id`, `translator_id`, `transaction_code`, `is_accepted`, `accept_date`, `accept_date_persian`, `order_step`, `is_done`) VALUES
(3, 21, 1, 'knegbnrgb35', 1, NULL, NULL, 1, 0),
(4, 22, 0, 'fkrjvenvo56', 0, NULL, NULL, 2, 0),
(5, 23, 1, 'vevnv,ernv56', 1, '1398/01/02 23:30', '1398/01/02 23:30', 2, 1);

-- --------------------------------------------------------

--
-- Table structure for table `payment_logs`
--

CREATE TABLE `payment_logs` (
  `id` int(11) NOT NULL,
  `translator_id` int(11) NOT NULL,
  `amount` varchar(10) NOT NULL,
  `refer_code` varchar(16) NOT NULL,
  `pyment_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `payment_date_persian` varchar(16) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `payment_logs`
--

INSERT INTO `payment_logs` (`id`, `translator_id`, `amount`, `refer_code`, `pyment_date`, `payment_date_persian`) VALUES
(1, 1, '10000', 'jvjlvjlvj,bk,s', '2019-03-11 20:30:00', NULL),
(2, 1, '120300', 'cklzdjvldsfjb', '2019-03-03 20:30:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `title` varchar(250) NOT NULL,
  `previewText` mediumtext NOT NULL,
  `link` varchar(100) NOT NULL,
  `date` varchar(10) NOT NULL,
  `thumbnail` varchar(200) NOT NULL,
  `categories` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `title`, `previewText`, `link`, `date`, `thumbnail`, `categories`) VALUES
(1, 'آموزش کامل ایجاد و تغییر پاورقی در ورد', '<p>در بسیاری از نوشته ها از جمله تالیف پایان نامه، مقاله، کتاب و حتی تحقیق، نیاز به این دارید که از پاورقی برای توضیح برخی اصطلاحات و عبارات استفاده کنید. نرم افزار WORD به عنوان قوی ترین برنامه ویرایش متن دارای امکان گذاشتن پاورقی در&#8230; </p>\n', 'http://www.motarjem1.com/blog/?p=2106', '14 بهمن 13', 'http://www.motarjem1.com/blog/wp-content/uploads/2019/02/Microsoft-Word-w600-350x195.jpg', '[{\"link\":\"http:\\/\\/www.motarjem1.com\\/blog\\/category\\/%d8%a2%d9%85%d9%88%d8%b2%d8%b4%db%8c\\/\",\"name\":\"\\u0622\\u0645\\u0648\\u0632\\u0634\\u06cc\"}]'),
(2, 'ترجمه تخصصی مقالات رشته کامپیوتر در مترجم وان', '<p>همانطور که می دانید رشته های مرتبط با تکنولوژی همه روزه در حال پیشرفت و نوآوری های جدید هستند. بنابراین برای اینکه بتوانید در این رشته ها پیش بروید نیاز به این دارید که دائما علم خودتان را به روزرسانی کنید. رشته کامپیوتر در راس&#8230; </p>\n', 'http://www.motarjem1.com/blog/?p=2096', '11 بهمن 13', 'http://www.motarjem1.com/blog/wp-content/uploads/2019/01/Computer-Sciences-Translation-Tarjomeyar-Com-350x195.jpg', '[{\"link\":\"http:\\/\\/www.motarjem1.com\\/blog\\/category\\/%d8%a2%d9%85%d9%88%d8%b2%d8%b4%db%8c\\/\",\"name\":\"\\u0622\\u0645\\u0648\\u0632\\u0634\\u06cc\"}]'),
(3, 'نکات مهم در تقویت رایتینگ جهت موفقیت در آزمون آیلتس', '<p>همانطور که می دانید قبولی در آزمون آیلتس آکادمیک و آیلتس جنرال نیاز به این دارد که شما تمامی مهارت های زبان انگلیسی تان را تقویت کنید. رایتینگ زبان یکی از مهارت های مهم در این آزمون می باشد. در این مقاله نکات مهمی را&#8230; </p>\n', 'http://www.motarjem1.com/blog/?p=2085', '7 بهمن 139', 'http://www.motarjem1.com/blog/wp-content/uploads/2019/01/تقوی-نوشتن-350x195.jpg', '[{\"link\":\"http:\\/\\/www.motarjem1.com\\/blog\\/category\\/%d8%a2%d9%85%d9%88%d8%b2%d8%b4%db%8c\\/\",\"name\":\"\\u0622\\u0645\\u0648\\u0632\\u0634\\u06cc\"}]');

-- --------------------------------------------------------

--
-- Table structure for table `study_fields`
--

CREATE TABLE `study_fields` (
  `id` int(11) NOT NULL,
  `title` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `study_fields`
--

INSERT INTO `study_fields` (`id`, `title`) VALUES
(0, 'عمومی'),
(41, 'ترجمه کاتالوگ'),
(43, 'زیرنویس فیلم'),
(44, 'فایل صوتی تصویری'),
(46, 'ورزش و تربیت بدنی'),
(47, 'نفت،گاز و پتروشیمی'),
(49, 'مدیریت'),
(50, 'متالورژی و مواد'),
(51, 'محیط زیست'),
(53, 'مکانیک'),
(54, 'منابع طبیعی و شیلات'),
(55, 'کامپیوتر'),
(56, 'کشاورزی'),
(57, 'فقه و علوم اسلامی'),
(58, 'فلسفه'),
(59, 'فناوری اطلاعات'),
(60, 'فیزیک'),
(61, 'عمومی'),
(62, 'علوم اجتماعی'),
(63, 'علوم سیاسی'),
(64, 'عمران'),
(67, 'شیمی'),
(68, 'صنایع'),
(69, 'صنایع غذایی'),
(70, 'روان شناسی'),
(71, 'ریاضی'),
(72, 'زمین شناسی و معدن'),
(73, 'زیست شناسی'),
(74, 'حقوق'),
(75, 'حسابداری'),
(76, 'جغرافیا'),
(85, 'پزشکی'),
(86, 'برق و الکترونیک'),
(88, 'اقتصاد'),
(89, 'اسناد تجاری'),
(90, 'ادبیات و زبان شناسی'),
(91, 'تاریخ'),
(92, 'هنر و معماری'),
(93, 'ژنتیک و میکروبیولوژی'),
(94, 'نساجی'),
(95, 'زیست شناسی'),
(96, 'معماری');

-- --------------------------------------------------------

--
-- Table structure for table `tests`
--

CREATE TABLE `tests` (
  `id` int(11) NOT NULL,
  `study_field_id` int(11) DEFAULT '0',
  `language_id` tinyint(1) DEFAULT '0',
  `text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tests`
--

INSERT INTO `tests` (`id`, `study_field_id`, `language_id`, `text`) VALUES
(1, 74, 1, 'In order to articulate the financing mentioned in the First Clause, â€œTHE CONSULTANTâ€ will provide the following services:\n\n\n\nReview and analyze all documents provided by â€œTHE CLIENTâ€ regarding financial reports, technical details, internal information and any other material that is considered relevant to achieve the purpose of this Contract.\n\nProvide advice to â€œTHE CLIENTâ€ in all matters related to the potential financing schemes for â€œTHE PROJECTâ€ in order to find the most suitable option for the purposes of â€œTHE CLIENTâ€.\n\nPromote â€œTHE PROJECTâ€ among investors, financial institutions, capital funds or any other entity that according to his own experience would be interested in and be capable of finance it.\n\nNegotiate the best possible terms and conditions of the loan and financial structures until its completion by means of the signing of an official Contract or Agreement.\n\nIf necessary, find a private equity investor to participate in the project. The terms and conditions of such participation will be consulted with and approved by â€œTHE CLIENTâ€. All communications related to this matter shall be made in writing.\n\n'),
(2, 64, 1, 'Several contemporary architectural works are arguing use of parametric design technologies, without clearly identifying their essential conditions. This article reviews meanings of this term and its first literary uses  in referring to architectural design, as well as initial works applying  these techniques, with the purpose of clarifying its original sense and  applications, to support a consistent development. The word has different  meanings ranging from social to mathematical connotations, mostly related  to a measurable variation. Meanwhile, the documents about architectural  works consider the concept of parametric design from the stand point of  management of building information to specific geometric operations.  The works reviewed as early examples of parametric procedures in  building design are the Philips Pavilion by Le Corbusier and Xenakis, the  Barcelona Fish by Frank Gehry and the Extension of Waterloo Station by  Nicholas Grimshaw. It comments on their general process of production,  functional and construction issues involved in the design of these three  cases. Geometrical and computer procedures are described in relation  to shape definition, structural solution and design expression of each  building. Based on the texts and cases reviewed, the article suggests a strict  conception of parametric design in architecture, linked to variable curves,  as well as a constructive and cultural sense.\n\n\n\n'),
(3, 75, 1, 'There is reliable evidence that managers smooth their reported earnings. If some firms manage earnings downwards (upwards) when they experience large positive (negative) earnings shocks and if investors have cognitive limits or are inattentive, then it is plausible that the post-earnings announcement drift could be related to earnings management. Consistent with this conjecture, we find that firms with large negative (positive) changes in operating cash flows manage their accruals substantially upwards (downwards). Most importantly, we find no evidence of a positive post-earnings announcement drift for those firms with large positive earnings changes that are least likely to have managed earnings downward or a negative post-earnings announcement drift (PEAD) for those firms with large negative earnings changes that are least likely to have managed earnings upward. That is, for these firms, there is no evidence of an underreaction to earnings changes. The underreaction is concentrated largely among those firms that are most likely to have smoothed their reported earnings, although this effect has weakened in recent years as investors started paying more attention to the anomalies and hedge funds were focusing on exploiting them.\n\n'),
(4, 94, 1, 'Anionic (acid) dyes are commonly used to colour nylon [1-2]. These attach to nylon via electrostatic linkages between the cationic, protonated amino end groups of the nylon (NH3)\n\nand the anionic sulphonate groups of the dye (Dye-SO3-). However, in this type of attachment the wet fastness is usually less than ideal and some staining of adjacent fabrics takes place during laundering. The first commercially successful covalent attachment of dyes to textiles is generally attributed to Rattee and Stephen [3-6] who demonstrated that dyes containing a dichlorotriazine reactive group could be covalently attached to cotton [7-10]. Nowadays reactive dyes, which incorporate one or more electrophilic reactive groups, are the dominant class used for dyeing cellulosic fibres. However, only relatively small quantities of reactive dye are used on nylon and the focus of most of the published work has been a comparison of the relative efficacies, on nylon, of existing water soluble cellulose reactive dyes possessing electrophilic reactive groups such as chlorodifluoropyrimidines.\n\n'),
(5, 53, 1, 'Yu et al. [4] presented a thermo-mechanical model for the prediction of angular deformations of metal plates due to laser line heating. Their model employed a semi-analytically determined temperature distribution, which incorporated the effects of heat loss and a distributed moving heat source, to calculate the dimensions of a critical heat-affected region. They used dimensions of this region to find the angular deformation by an analytic solution method. Shen et al. [5] derived a formula of bending angle in laser forming based on the assumptions that the plastic deformation is generated only during heating, and during cooling only the elastic deformation occurs. In this formula, the yield strength reduction factor due to the temperature increase and the characteristic depth of plastic zone are involved. Mucha [6] presented an analytical model of laser plate bending based on temperature gradient, in which the restrain rigidity coefficient was defined. The model gave the solutions for longitudinal and transversal angle deformation.\n\n'),
(6, 68, 1, 'Most of the research with deteriorating jobs focuses on single-machine problems. Often, jobs consist number of operations to be done serially in many manufacturing systems [19â€“23]. The deteriorating jobs scheduling is relatively unexplored in the flowshop environment. Wang and Xia [24] addressed no-wait or no-idle flow shop scheduling problems. They showed that the problems to minimize makespan or weighted sum of completion time are more complicated than the classical ones. Lee et al. [25] considered the total completion time problem on permutation flowshop environment. Wang and Liu [26] considered the two-machine total completion time problem. They derived the optimal solution for some special cases, provided a branch-and-bound and a heuristic algorithm for the general case. Sun et al. [27] studied the permutation flow shop scheduling problems on no-idle dominant machines. They provided the optimal solutions for the makespan and the total completion time problems. Lee et al. [28] studied a two-machine flowshop problem with blocking where the objective is to minimize the makespan. Rudek [29] studied a two-machine flowshop makespan problem with learning consideration, in which the computational complexity was proved. Ng et al. [30] considered a two machine flowshop problem where the objective is to minimize the total completion time. They derived a branch-and-bound and a heuristic algorithm. Wang et al. [31] considered the makespan problem with a simple linear deterioration on a three-machine permutation flowshop. They derived the optimal solution for some special cases, and provided a branch-and-bound and a heuristic algorithm for the general case. Zhao and Tang [32] considered two types of precedence constraints in two machine scheduling problems with deteriorating jobs. Bank et al. [33] proposed a branch-and-bound algorithm to minimize the total tardiness in two-machine flowshop environment, in which the processing time of a job depends on its waiting time.\n\n'),
(7, 67, 1, 'The results of differential scanning calorimetry illustrate that nanometer-sized copper oxide and ferric oxide have a significant catalytic effect on the thermal decomposition\n\nof ammonium perchlorate. The presence of these nano-sized metal oxides reduces significantly the higher decomposition temperature of ammonium perchlorate. With increase of content of nanometer-sized metal oxide, the decrease in higher decomposition temperature of ammonium perchlorate becomes greater. Also, the catalytic effect of nano-sized copper oxide with larger particle size is more sizable than that of the nano-sized ferric oxide.\n\nThe CuO nanoparticles were blent with AP in different contents of 1, 2, and 3 wt.% to prepare the samples for thermal decomposition experiments. These samples were labeled as AP1C (AP + 1% nano-CuO), AP2C (AP + 2% nano-CuO), and AP3C (AP + 3% nano-CuO). Also, in a similar way, the Fe2O3 nanoparticles were mixed with AP, and the samples were labeled as AP1F (AP + 1% nano-Fe2O3), AP2F (AP + 2% nano-Fe2O3), and AP3F (AP + 3% nano-Fe2O3). Before the thermal decomposition experiments using DSC technique, the samples were homogenized. Figures 5 and 6 show the samples used in this study.\n\n'),
(8, 47, 1, 'In addition, the maximum solid packing in radial distribution function is one of the geometrical properties of particles, which indicates the maximum value of solid volume fraction. It is also an important parameter which affects the plastic flow regime, solid granular pressure, and the solid-solid drag model [76]. Radial distribution function represents a correction factor to modify the probability of particles colliding when the particle volume fraction becomes dense. In addition, it represents the transition of compressible fluidization (s<smax)  to incompressible flow ( s=smax ). Radial distribution function in turn the viscous solid pressure pushing the particle towards less dense region to prevent the un-physically high concentration regions of the bed. Note that the convergence of solution is improved using the specify maximum solid volume fraction near the dense region of the bed [77]. In addition, the multi fluid simulations showed a strong dependency of the maximum solid packing on the minimum fluidization velocity, particle collision [78], and bed height profile. Settling and decreasing of the bed height observed using the higher maximum solid packing than solid volume fraction [77]. However, the maximum solid packing is defined based on the experimental condition. Some correlations are also suggested [79] while Chang et al. [80] and Chen et al. [5] used same and higher value than solid volume fraction due to fill the interstitial spaces between the larger particles. Therefore, special care is necessary to specify maximum solid packing in CFD-PBM coupled model simulations.\n\n\n\n'),
(9, 71, 1, 'An orthogonal latin square graph is a graph whose vertices are latin squares of the same order, adjacency being synonymous with orthogonality. In 1979 Lindner, Mendelsohn, Mendelsohn, and Wolk [47] proved that any finite graph can be realized as an orthogonal latin square graph. We are interested in a special class of orthogonal latin square graphs, those based on finite groups. For our purposes an orthogonal latin square graph will be said to be based on the group G if each square in the graph is orthogonal to the Cayley table of G, and each square in the graph is obtained from the Cayley table of G by permuting columns. Note that in an orthogonal latin square graph based on a group, each square is uniquely determined by the entries in its first row.\n\n'),
(10, 69, 1, 'Postharvest fruit and vegetable are living organisms, undertaking metabolism ceaselessly. Their character such as nutrition, favor, and appearance deteriorated during the process of storage and transportation owing to water loss, browning, decay, and so on [1,2]. Thus, the commercial value also decreases and many damages are caused to producer. To extend the shelf life of postharvest fruit and vegetable, some effective measures including low temperature, modified atmosphere packaging, irradiation and coating, have been applied [3-5]. In those measures, edible coating is one of promising methods because of its particular properties, which could avoid moisture loss and aromas loss, and inhibit the oxygen penetration to the plant tissue or microbial growth. In addition, edible coating is convenient and conforms to food safety [6]. Many materials such as polysaccharides, proteins, essential oils, may economically serve as edible coatings [7-9].\n\n'),
(11, 85, 1, 'The International Cooperative Pulmonary Embolism Registry (ICOPER), which included 2452 patients from 52 centers in seven countries, suggests a 17.4% 90-day mortality among all patients suffering from a PE. The same report suggests a 4.5% incidence of massive PE with a 52.4% associated mortality and a strong correlation with right ventricular (RV) dysfunction, RV thrombi, and congestive heart failure (CHF). To date, clot extraction and/or dissolution by either percutaneous or open-surgical technique has been reserved as a last resort treatment for hemodynamically unstable patients, often with severe RV dysfunction, or for those who are either not candidates for or have failed thrombolysis. Recently, several major centers have liberalized the use of surgical embolectomy to include patients with PE associated with moderate-to-severe RV dysfunction without hemodynamic compromise. RV dysfunction alone, documented by echocardiography, has been implicated as an early and late independent risk factor for RV failure and mortality in a number of studies, and recovery of its function has been identified as an early predictor of a favorable in-hospital course. Several recent retrospective reviews have addressed the role of surgical pulmonary embolectomy while emphasizing recent advances in diagnosis, surgical technique, and postoperative care.\n\n\n\n'),
(12, 72, 1, 'Three-dimensional (3D) geological, geostatistical, and fractal/multifractal modeling are combined for the identification of new exploration targets in the Tongshan porphyry Cu deposit (China): (1) A 3D geological model of the deposit includes the strata, faults, altered rocks, intrusive bodies, and three orebodies using geological map, cross-sections, borehole dataset, and magnetic inversion; (2) geostatistical analysis involves omnidirectional and vertical semi-variogram calculations of the orebody, ordinary kriging interpolation of the orebody and 3D trendmodeling using the assay data; (3) fractalmodels consisting of Hurst exponent estimation of the continuity of vertical mineralization and its concentrationâ€“volume(Câ€“V) fractalmodel separation mineralized zones in a 3D block model; and (4) interpretation and validation: magnetic inversion was utilized to constrain intrusive rock shape between cross-sections and additional interpret orebody geometry model by ordinary kriging interpolation method using Tongshan borehole dataset. The results indicate that (a) the Hurst exponent is useful for identifying the vertical continuity of mineralization (with the range between 0 and 1200 m), (b) the Câ€“V fractal model is useful for identifying thresholds of Cu values in oxidation-type, skarn-type, and magmatic-type orebodies in the Tongshan deposit, and (c) the 3D geological and trend model can be combined to recognize potential subsurface targets in the Tongshan deposit. The methods can be applied to estimate mineral resources through district-scale exploration.\n\n'),
(13, 86, 1, 'A number of countries have taken specific initiatives to de-carbonize their electrical power system by encouraging wind generation. In UK, there may be up to 30 GW of wind generation within a total generation capacity of some 100 GW serving a load of around 60 GW by 2020 [1]. A high penetration of wind energy will increase the difficulty in the unit commitment (UC) of power system. Without effective management, the economy of wind power integration will decrease and the system operation cost will increase. As a new kind of controllable load, Electric vehicles (EVs) have the potential to increase the capability of residential consumers to participate in the demand response scheme. Under the vehicle-togrid (V2G) concept, the EVs can not only act as a rapid responsive load, but also serve as an energy storage system for supporting the operation of power system [2]. In this paper, an efficient power plant model of EVs (E-EPP) is developed to evaluate the real-time available V2G capacity during a day. Under a new developed UC strategy, the E-EPP can respond to the fluctuations of power system caused by the integration of large scale wind farms, which can significantly decrease the frequent power variations of traditional generators. The E-EPP can promote the utilization of wind power and improve the stability of power system.\n\n'),
(14, 58, 1, 'A problem which has arisen frequently in contemporary philosophy is: \"How are contingent identity statements possible?\" This question is phrased by analogy with the way Kant phrased his question \"How are synthetic a priori judgments possible?\" In both cases, it has usually been taken for granted in the one case by Kant that synthetic a priori judgments were possible, and in the other case in contemporary philosophical literature that contingent statements of identity are possible. I do not intend to deal with the Kantian question except to mention this analogy: After a rather thick book was written trying to answer the question how synthetic a priori judgments were possible, others came along later who claimed that the solution to the problem was that synthetic a priori judgments were, of course, impossible and that a book trying to show otherwise was written in vain. I will not discuss who was right on the possibility of synthetic a priori judgments. But in the case of contingent statements of identity, most philosophers have felt that the notion of a contingent identity statement ran into something like the following paradox.\n\n'),
(15, 92, 1, 'recently was asked to post something about reverb and how to use it best. Iâ€™m not going to claim to know all there is to know about reverb but Iâ€™ll do my best to share what I know. Iâ€™ll also try not to use too much geek speak when explaining things. letâ€™s start by defining some of the most important features.\n\nReverb decay â€“ This is how you control how long the reverb lasts before it fades out. Depending on what you are going for, this can be long and washy, or short and barely noticeable. keep in mind though that even though you might not immediate hear what a reverb is doing, the brain is exceptional at picking up these subtleties & can make a huge difference between a flat, sterile and lifeless sound & a realistic sound. By controlling these, itâ€™s possible to use reverb rhythmically. You can have it fade after a 1/4 bar or 1/2 bar. Slightly adjusting this can create a bit of swing or groove to your mix.\n\n'),
(16, 76, 1, 'Urbanisation is a key driver of land use change and urban growth is set to continue. The provision of ecosystem services depends on the existence of green space. Urban morphology is potentially an im- portant inï¬‚uence on ecosystem services. Therefore, it may be possible to promote service provision through an urban structure that supplies the processes and functions that underpin them. However, an understanding of the ability of urban areas to produce multiple ecosystem services, and the spatial pattern of their production, is required. We demonstrate an approach using easily accessible data, to generate maps of key urban ecosystem services for a case study city of Shefï¬eld, UK. Urban green space with a mixture of land covers allowed areas of high production of multiple services in the city center and edges. But crucially the detection of such â€˜hotspotsâ€™ depended on the spatial resolution of the mapping unit. This shows there is potential to design cities to promote hotspots of production. We discuss how land cover type, its spatial location and how this relates to different suites of services, is key to promoting urban multifunctionality. Detecting trade-offs and synergies associated with particular urban designs will enable more informed decisions for achieving urban sustainability.\n\n\n\n'),
(17, 57, 1, 'Religious identity has, in recent times, become an important point of inquiry because of the growing awareness of religious diversity. On the one hand, this reality of diversity has served as an impetus to return to the roots of oneâ€™s religion. On the other hand, others have called for a more pluralist stance, out of the need to open up to other traditions. In light of this polarity, I argue that one can commit to oneâ€™s religion while opening up to the religious other in a way that does not threaten oneâ€™s own tradition. This is done through a hermeneutic analysis of the religious identity, taking off from Paul Ricoeurâ€™s phenomenological hermeneutics â€“ how this identity is formed and informed by the different significations of meaning within the tradition, and how the believer interacts with this tradition to construct his or her own narrative identity, through his or her imagination, mapping out the constellations of possible human action that root themselves in the necessity in encountering and working with the religious other, for this necessity is constitutive of oneâ€™s commitment to the tradition, embodied in the biblical narratives that call for this encounter. In sum, it is possible to be committed to oneâ€™s faith conviction while being hospitable to the religious other because it is constitutive of religion itself to encounter its other, and it is in this encounter that faith is truly understood as conviction.\n\n'),
(18, 55, 1, 'Mobile Adhoc Networks is a self-organizing network composed of mobile terminals connected by wireless links. Adhoc Networks are created dynamically without any preexisting network infrastructure Ad-hoc networks are very useful in situations like emergency search and reuse operations and meetings where people want to quickly share information. This network does not have any central administration, hence there are no designated routes, all the nodes can serve as routers for each other, and data packets are forwarded from node to node to node in multi hop fashion. Cluster based Mobility and Energy Aware (CMEA) routing protocols is used to find the reliable route in a cluster networks. The clustered network avoids path breaks and long path delay. When applying the energy and mobility aware metric over clustered network gives reliable route between Source and Destination. Service Oriented Architecture (SOA) is an evolution of past platforms, preserving successful characteristics of traditional architectures and bringing with it distinct principles that follow service orientation in support service oriented enterprise. It forces the network to improve the reliability, security, integrity etc. By applying the proposed XML technique for encrypting the secure data improves the content delivery over the CMEA network. Therefore SOA based secure data transmission over the reliable route improves the network performance.\n\n'),
(19, 50, 1, 'The underlying principles for the cutting tool tribology considerations constitute a unique viewpoint from which the subject is considered. They unify various facets of such considerations establishing physically-sound criteria at each stage. The underlying principles include the definition of the metal cutting process and the basic laws. The basic laws in any branch of applied science establish its foundation principles. For example, the laws of thermodynamics are amongst the simplest, most elegant, and most impressive products of modern science. Most physical laws are designed to explain processes which humans experience in nature. The best and the most general way to establish such laws is to consider the energy flow and transformation in a technical system distinguished by a given science because only system considerations result in physics-based and sound laws.\n\n'),
(20, 50, 1, 'SiC ceramics are considered one of the most promising structural materials for special applications. TiAlbased alloys have a great potential to become important candidates for advanced applications in aerospace and military industries. There have been many reports on diffusion bonding and brazing of SiC ceramics to metals [1, 2]. The researches on diffusion bonding and brazing of TiAl-based alloys to other materials have progressed in recent years [3â€“5]. The concept of utilizing ceramic, intermetallic and metallic materials to attain one complete armor system by bonding process is a recent approach for defeating armor projectiles [6, 7]. Therefore, a previous study of brazing of SiC ceramic to TiAl-based alloy was carried out [8]. This letter aims to demonstrate the feasibility of diffusion bonding of SiC ceramic to TiAl-based alloy, and the focus is placed on the interface structures, bonding strengths and fracture paths of the joints.\n\n'),
(21, 46, 1, 'Chromium is an essential trace mineral and has many roles in metabolism. In relation to sports performance, its role in enhancing the action of insulin, which is required for uptake of glucose and amino acids into the cell, has implications for enhancing glucose oxidation and recovery. Other claims in relation to its action on insulin are increases in muscle mass and strength. It has been proposed that chromium supplementation during exercise, mainly as chro mium picolinate, the most active form, enhances carbohydrate metabolism and promotes glycogen resynthesis, hence speeding up recovery of fuel reserves. Studies on these effects have not supported this hypothesis. Adding chromium picolinate to a sports drink provided no additional effect on carbo hydrate metabolism above that of the carbohydrate content in the sports drink. Other claims of habitual use of chromium supplements to increase muscle mass and strength and reduce body fat have not been substantiated in well-designed studies using a control group (see Chapter 9). Athletes with restricted energy intakes are most at risk of low chromium intakes. \n\n'),
(22, 56, 1, 'The procedure of plant regeneration involving callus induction, adventitious shoot formation, shoot elongation, and rooting is shown in Table 2. Basal media used for callus induction include DCR, BMS, LP, MS, SH, and TE media. The frequency of callus formation is determined 6 weeks after culture. After calli are transferred onto adventitious shoot regeneration medium consisting of DCR, BMS, LP, MS, SH, and TE media for 6 weeks (Table 1), differentiation is evaluated by the percentage of calli forming adventitious shoots on the medium for a 6-week period.\n\n1. Subculture calli every 3 weeks before the induction of shoot formation.\n\n2. Transfer calli onto shoot formation medium supplemented with IBA, BA,\n\nand TDZ for 2Â–3 subcultures. If more calli are needed, subculture calli 4Â–6\n\ntimes.\n\n\n\n'),
(23, 70, 1, 'This study focuses on helping students with ASD negotiate one specific challenge of mainstream education: the transition from primary to secondary school. In most industrialised countries, this takes place as students approach adolescence, when they are 11 or 12 years of age. Compared to primary schools, secondary schools tend to be larger and to make greater demands on their pupilsâ€™ independence, with a stronger focus on self-directed learning and academic assessment (Coffey, 2013). A child in primary education receives most of their teaching from a single class teacher, in one room surrounded by a familiar group of peers. By contrast, at secondary school, students have to follow a timetable to navigate around the school campus throughout the day, receiving instruction from multiple teachers. As such, the move from primary to secondary school places substantial social, intellectual, organizational and emotional demands upon pupils and is considered to be one of the greatest challenges in a young personâ€™s educational career (Zeedyk et al., 2003).\n\n'),
(24, 49, 1, 'Al Fawzan (2003) studies the modern information systems and their impact on the performance of employees â€“ a survey on the General Customs Authority, Saudi Arabia. This study aimed know the sources of information flow in the Customs Department, and the identification and classification of internal and external information of interest, and find out the positive role of systems use modern information on the performance of employees, as well as knowledge of the negative role of the systems use modern information on the performance of employees, Among the most important findings of the study 61% of respondents do not know for specialized training programs in the field of modern information technology, and answered 24.2% of respondents said that it is not already present in the training programs, Lack of knowledge of staff interest in e-commerce, Endorsed by 91.5% of respondents believed that the use of modern information systems will contribute to the accuracy of the business, Approved by 87% of respondents believed that in the event of use slept interest information will improve the performance of modern interest, Approved by 87% of respondents believed that the use of modern information systems will facilitate the work of the staff, The majority of respondents agreed that there are\n\nadministrative and financial constraints, operational and psychological facing the use of modern management information systems of interest.\n\n'),
(25, 94, 1, 'MicroRNAs (miRNAs) are small, endogenous noncoding RNA molecules that contribute to modulating the expression level of specific proteins based on sequence complementarities with their target mRNA molecules. Most of miRNA species identified thus far are encoded in portions of the genome that had been previously thought to be noncoding regions. Since first discovered in 1993, miRNAs have attracted wide attention due to their unique functional significance and modes of action, providing a new dimension of, and the latest addendum to, the central dogma of molecular biology. Because one miRNA can target multiple mRNA targets, it is estimated that more than one third of human genes are regulated by miRNAs, and the number of genes found to be under the modulation of miRNAs has increased sharply. The role of miRNAs as key regulatory molecules that control a wide variety of fundamental cellular processes, such as proliferation, death, differentiation, motility,\n\ninvasiveness, etc., is increasingly recognized in almost all fields of biological and biomedical fields. Understanding the significance of miRNAs in the pathogenesis of human diseases represents an important dimension in miRNA research as it may lead to the development of miRNA-based novel therapeutic strategies or diagnostic/ prognostic biomarkers. Among diseases that most seriously threaten human lives, cancer, which has been found by recent studies to be associated with deregulation or genetic changes of miRNAs as previously reviewed by others (1, 2), apparently represents an outstanding public health problem that causes 7.6 million deaths annually as estimated by WHO. This review attempts to briefly outline our current knowledge on the abnormalities of miRNAs found to be associated with cancer pathogenesis and possible mechanisms underlying the roles of miRNAs in cancer development and progression and to provide a perspective insight in using miRNAs as cancer biomarkers and therapeutic targets or tools.\n\n\n\n'),
(26, 64, 1, 'This paper investigates the along-wind forced vibration response of an onshore wind turbine. The study includes the dynamic interaction effects between the foundation and the underlying soil, as softer soils can influence the dynamic response of wind turbines. A Multi-Degree-of-Freedom (MDOF) horizontal axes onshore wind turbine model is developed for dynamic analysis using an Eulerâ€“Lagrangian approach. The model is comprised of a rotor blade system, a nacelle and a flexible tower connected to a foundation system using a substructuring approach. The rotor blade system consists of three rotating blades and includes the effect of centrifugal stiffening due to rotation. The foundation of the structure is modeled as a rigid gravity based foundation with two DOF whose movement is related to the surrounding soil by means of complex impedance functions generated using cone model. Transfer functions for displacement\n\nof the turbine system are obtained and the modal frequencies of the combined turbine foundation system are estimated. Simulations are presented for the MDOF turbine structure subjected to wind loading for different soil stiffness conditions. Steady state and turbulent wind loading, developed using blade element momentum theory and the Kaimal spectrum, have been considered. Soil stiffness and damping properties acquired from DNV/RisÙ‘ standards are used as a comparison. The soil-structure interaction is shown to affect the response of the wind turbine.\n\n'),
(27, 62, 1, 'Culture in a community is defined in many ways. The most simple of definitions is that culture represents the collective \"soul of a community. As distinguished from vocational or technical skills, culture is closely connected with a human\'s capacity for learning and transmitting knowledge to succeeding generations. Communities create institutions which develop or enrich the lives of its residents by promoting intellectual and aesthetic improvement, These institutions are used to transfer, communicate and pass along commonly held ideas, customs, skills, values and heritage of those in the community.\r\nThe way in which a community represents its culture through its institutions and infrastructure says a great deal about the shared attitudes, values, goals and vision of its residents. Because of this, the community\'s entire identity is merely a reflection of its attitudes about and investment in those things that are defined as cultural.'),
(28, 75, 2, 'هدف از انجام این تحقیق بررسی و تحليل رفتار سودآوري شرکت ها مبتني بر قاعده بازگشت به ميانگين یا گام تصادفي در نمونه ای مورد مطالعه از شرکت های تولیدی پذیرفته شده در بورس اوراق بهادار تهران بود. داده‌های تحقیق برای دوره شش ساله (1387 الی 1392) از منابع صورت های مالی حسابرسی شده و نرم افزار ره آورد نوین گردآوری شد. نمونه مورد مطالعه 172 شرکت با اعمال شرایط از جامعه آماری انتخاب شد. در راستای دستیابی به هدف تحقیق سه فرضیه مطرح شد. تحلیل داده ها و آزمون فرضیه ها با استفاده از روش پانل دیتا در نرم افزار Eview انجام شد. به منظور بررسی خاصیت بازگشت به میانگین از آزمون ریشه واحد (ديكي فولر تعميم يافته) و در جهت بررسی خاصیت گام تصادفی از آزمون نسبت واریانس در سه نسبت سودآوری بازده دارایی ها، بازده حقوق صاحبان سهام و بازده سرمایه گذاری ها بهره گرفته شد. بر اساس داده های گردآوری شده از صورت های مالی و تحلیل در نرم افزار آماری نتایج حاصل از آزمون فرضیه ها در سطح خطای پنج درصد نشان داد سري زماني شاخص سودآوری بازده دارایی ها داراي خاصيت بازگشت به ميانگين است. سري زماني شاخص سودآوری بازده حقوق صاحبان سهام داراي خاصيت بازگشت به ميانگين است. سري زماني شاخص سودآوری بازده سرمایه گذاری ها داراي خاصيت بازگشت به ميانگين است\n\n'),
(29, 62, 2, 'در تحقيقات انجام شده داخل كشور نيز همچون تحقيقات خارجي سعي برآن شده است در هر تحقيق بر روي يك متغيير تمركز داشته باشند. كه در اين مقاله به طور مختصر به چند تحقيق اشاره مي شود. احمد غرويان در سال 1378 با بررسي مقوله تصميم گيري، الگو هاي ارتباطي، نگرش افراد نسبت به رضايت شغلي به اين نتيجه رسيده است  که روابط رسمی بیشتر از روابط غیر رسمی در سازمان جاری است. مظاهر ضيايي در بررسي تاثير ريشه هاي فرهنگي بر تحول سازماني به اين نتيجه مي رسد كه مقاومت در برابر تغيير وجود دارد، اعتماد کم به دیگران، بدبینی نسبت به آینده، مشکل بودن کار تیمی در بین ایرانیان با تحول سازمانی رابطه معنا داری دارد. بهروز قليچ لي با بررسي تاثير سرمايه اجتماعي بر روي سرمايه فكري نشان مي دهد که سرمایه اجتماعی بر سرمایه فکری به طور کلی و هم چنین هر یک از عناصر آن شامل سرمایه انسانی، سرمایه ساختاری، سرمایه رابطه ای در دو شرکت مورد مطالعه تاثیر مثبت و معنی داری دارد. همچنين نقش سرمايه اجتماعي در توسعه مديريت دانش سازماني تحقيق ديگري است كه محمد مهدي فراحي انجام داده است، یافته ها نشان می دهد که سرمایه اجتماعی بر روی آن دسته از فعالیت های مدیریت دانش تاثیر می گذارد که بیش هر چیز نیازمند وجود فرهنگ مناسب، تعامل اثر بخش و اعتماد متقابل میان افراد سازمان باشد.\n\n'),
(30, 47, 2, 'انسداد غشايي مهم ترين عامل محدودكننده در استفاده از ميكروفيلتراسيون در صنعت ماءالشعير به شمار مي آيد زيرا تركيبات متنوعي نظير پروتئين ها، پلي فنل ها، كربوهيدرات ها بتاگلوكان و پنتوزان، ريز- و درشت-مولكول هاي عامل كدورت، تركيبات نيتروژن دار با وزن مولكولي بالا، سلول هاي مخمر، نمك هاي اگزالات و مقادير جزئي از مواد معدني در ماءالشعير منجر به ايجاد اين پديده میشوند  [5,6] .اخیراً موفقیت هایی در استفاده از تکنولوژی غشایی در شفاف سازی ماءالشعیر حاصل شده است و کاربرد صنعتی آن نیز آغاز گردیده است . این روش علاوه بر جایگزینی خاک دیاتومه با میکروفیلتراسیون متقاطع دارای مزایای متعدد دیگری نیز می باشد[14] . پیوستگی فرآیند ، کاهش مصرف مواد اولیه ، افزایش میزان تولید ، حذف استفاده از خاک دیاتومه ، کاهش فاضلاب ، پایداری کیفیت ، انعطاف پذیری ، کاهش اثرات مخرب حرارت بر کیفیت و طعم از جمله دلایلی است که موجب توجه روز افزون استفاده از غشاء در صنعت ماءالشعیر شده است.\n\n\n\n'),
(31, 60, 2, 'در این بخش ما نمودارهای طیف بازتابندگی را با استفاده از آرایش تجربی کرت اسکمن و راتر به ازای ضخامتهای مختلف لایههای نازک فلزات مس، نقره و طلا اندود شده بر روی منشور نسبت به زاویه تابشی جاروب شده برای طول موج تابشی 4348 آنگستروم بطور جداگانه نمایش دادهایم. در این نمودارها مشاهده کردیم مینیمم شدت بازتابندگی در زاویههای فرودی رخ میدهد که سرعت فاز اشعهی فرودی موازی محور  ها با سرعت فازمدهای سطح کوپل شود. اندازهگیری پهنا و عمق مینیمم بازتابندگی به ترتیب معیاری از اتلاف و شدت تحریک را ارائه میدهند. اتلاف پلاسمونهای سطحی در نتیجهی تلفات با مکانیسم نوسانات الکترون فونون در داخل فلز و بازتاب به داخل محیط منشوری میباشد. تلفات تابش به این دلیل اتفاق میافتد که دامنه مدهای سطحی به صورت نمایی در داخل فلز به طرف منشور، میرا میشود. اما یک مولفهی انتشاری هم در جهت  درون محیط منشوری دارد در نتیجه تلفات تابش ، تحت تأثیر ضخامت لایهی فلزی قرار خواهد گرفت. \n\n\n\n'),
(32, 76, 2, 'با افزایش جمعیت و گسترش شهر نشینی انسان از طبیعت دور شده و تراکم بیش از حد جمعیت، منجر به ایجاد نا هماهنگی در چگونگی استفاده از زمین شهری شده است. این مسئله دسترسی شهروندان به تسهیلات و خدمات عمومی (از جمله کاربری فضای سبز) را مشکل ساخته است(محمدی:1381) و نیاز به برنامه ریزی جهت مکان یابی عنصر کالبدی - فضایی شهر ها را مضاعف نموده است. امروزه زندگی در شهرها بیش از هر دوره دیگری وابسته به خدمات است . لذا با توجه به نقش روز افزون فعالیت های خدماتی در نظام شهر نشینی ، ضرورت جدیدی در روند برنامه ریزی شهری پدید آمده است و مسئله چگونگی پراکنش مراکز خدماتی و نحوه دسترسی به خدمات این گونه مراکز از اهمیت فراینده ای برخوردار شده است.(جمشید زاده:1378)از جمله خدمات شهری که امروزه کمبود و توزیع نا عادلانه در شهر های کشور ما احساس می شود فضای سبز شهری است. فضای سبز به مجموعه فضای سبز و آزاد که در داخل محیط های شهری با اهداف مشخص برنامه ریزی شده و عملکرد معینی بر عهده دارند اطلاق می شود(پور ابراهیم:1385)\n\n\n\n'),
(33, 67, 2, 'به منظور انتخاب یک جاذب مناسب پارامترهای متعددی مد نظر قرار می‌گیرند که برخی از آن‌ها عبارتند از ظرفیت جذب زیاد، سینتیک جذب و دفع سریع، قابلیت بازیابی و پایداری در شرایط مختلف عملیاتی. در دهه‌های اخیر جذب توسط مواد نانو متخلخل جدید از قبیل غربال‌های مولکولی، اکسیدهای فلزی، زئولیت‌ها و مواد متخلخل کربنی از جمله کربن‌های فعال، نانولوله‌های کربنی و نانوفیبرهای کربنی مورد توجه قرار گرفته است. از میان مواد متخلخل کربنی، نانولوله‌های کربنی ساختارهای نانو- توخالی بسیار معروفی هستند. ساختار لوله‌ای این مواد با قطرهای مولکولی و طول‌های ماکروسکپی سطوح وسیعی را در اختیار مولکول‌های جذب شونده قرار خواهد داد. حفرات استوانه‌ای نانولوله‌های کربنی باعث ایجاد نیروهای پیوندی قوی‌تری بین مولکول‌های جذب شونده با اتم‌های کربن دیواره نانولوله نسبت به یک سطح صاف گرافیتی خواهد شد. به علاوه نانولوله‌های کربنی بسیار گرافیتی بوده و سطوح آروماتیک آن‌ها حاوی دانسیته بالایی از الکترون‌های π می‌باشد که منجر به جذب قوی‌تر مولکول‌ها نسبت به کربن‌های فعال می‌شود.\n\n\n\n'),
(34, 94, 2, 'تا کنون مکانیسم مشخصی برای علت افسردگی عنوان نشده است. بیشتر پژوهشگران معتقدند که این بیماری به دنبال تغییر در میزان برخی از نوروترانسمیترهای اصلی در مغز ایجاد می شود که مهمترین آنها دوپامین، سروتونین و نور اپی نفرین است. بنابر این داروهایی که باعث متعادل ماندن سطح این نوروترانسمیترها می شوند، در درمان افسردگی موثر هستند(1). بوپروپیون(Bupropion) مهار کننده باز جذب سروتونین و نور اپی نفرین است و در گروه داروهای ضد افسردگی چند حلقه ای قرار دارد. این دارو از طریق مهار فعالیت پمپ های باز جذب کننده آمین (نور اپی نفرین یاسروتونین) در نورون های پیش سیناپسی مغز باعث افزایش سروتونین و نور اپی نفرین می شود (4).بوپروپیون در درمان افسردگی شدید و اختلالاتی نظیر اضطراب و بی خوابی طولانی مدت استفاده می شود . این دارو همچنین در درمان ترک سیگار نیز مفید است. از عوارض جانبی این دارو میتوان به اثر آن بر روی فعالیت دستگاه ادراری تناسلی اشاره کرد که اغلب باعث تکرر ادرار، ضعف جنسی و غیر طبیعی شدن انزال می شود(2و6).این دارو اثرات ضد افسردگی و نیز کاهش وابستگی به نیکوتین را از طریق گیرنده های استیل کولین که مرتبط با گیرنده های دوپامینی و نور اپی نفرینی هستند، ایجاد می کند. بوپروپیون اگر چه تمایل شاخصی برای اتصال به گیرنده های دوپامین، سروتونین و نور آدرنالین نشان نمی دهد ولی قادر است تمایل گیرنده های مذکور را به لیگاندهای اختصاصی تغییر دهد.همچنین این دارو به عنوان مهار کننده باز جذب دوپامین و نور آدرنالین بر روی یادگیری نیز اثر گذار است(3). \n\n'),
(35, 71, 2, 'در این تحقیق مسئله یافتن بهترین k جریان با مقدار صحیح رامورد بررسی قرار می دهیم . مسئله تعیین بهترین  جریان با مقدار صحیح در شبکه های جریان ایستا، یکی از مسائل اساسی شبکه های نقل وانتقال کالا یا شبکه های اطلاعاتی است . منظور از بهترین جریان ، جریانی است که نیاز مصرف کنندگان را از طریق مسیرهایی از گره های تولیدی به گره های مصرفی با کمترین هزینه انتقال برآورده می کند. جریانی که همین عمل را انجام می دهد وهزینه اش از بهترین جریان بیشتر ولی از جریان های دیگر کمتر است را دومین جریان بهتر گویند. به همین ترتیب k جریان بهتر درمسأله جریان با کمترین هزینه تعریف می گردد. در این تحقیق  مسئله k جریان برتر با مقدار صحیح ((k-Best Integer Solution in Network Flow(kbinf) را بررسی میکنیم هدف از این تحقیق ، تعریف الگوریتمی است کهk جریان بهتر و دارای مقدار صحیح را محاسبه نموده ودارای مرتبه زمانی قابل قبول نیز باشد. مسئله است . فرض بر این است که شبکه دارای n گره وm کمان می باشد .\n\n'),
(36, 55, 2, 'محاسبات ابری مدل ارائهی سرویس بر پایهی اینترنت است به طوری که دسترسی آسان بر اساس تقاضای کاربر به مجموعهای از منابع محاسباتی قابل تغییر را از طریق اینترنت برای کاربران فراهم می کند. برای مدیریت مناسب منابع ارائهدهنده‌ی خدمات، به توازن بار در محاسبات ابری نیازمندیم. توازن بار در محاسبات ابری، فرایند توزیع بار بین گره های محاسباتی توزیع شده به منظور استفادهی بهینه از منابع و کاهش زمان پاسخ است؛ تا موقعیتی پیش نیاید که برخی از گره ها سربارگذاری شده باشند و برخی دیگر بی کار باشند یا کار کمی انجام دهند. مهاجرت بار، یک راه حل بالقوه برای بسیاری از شرایط بحرانی در محیط ابر مانند عدم توازن بار میباشد. با این حال مهاجرت، معمولاً تنها بر اساس یک هدف صورت می‌گیرد. در عمل، در نظر گرفتن تنها یک هدف برای مهاجرت، می‌تواند در تضاد با دیگر اهداف باشد و ممکن است راه حل بهینه برای کار با وضعیت موجود، از دست برود. از طرفی، تحقيقات اندكي وجود دارد كه اهدافی را با هم در نظر گرفته‌اند، با اين حال نیز اشكالاتي در كارشان وجود دارد. (مثلا توان عملیاتی را با افزایش اندازه سیستم، افزایش نمی‌دهند، منجر به گرسنگی شود ، سیستم‌هایی که دارای تحمل خطا هستند را در نظر نمی‌گیرند، حجم بار ماشین مجازی در نظر نمی گیرند، افزایش مصرف انرژی دارند و ...) بنابراین ارائه یک استراتژی به منظور هدفمند سازی فرآیند مهاجرت بار در محیط ابر، براي كاهش هزینه و اثرات زيست محيطي و نیز جهت تطابق با شرایط مختلف ابری، ایده اصلی این پژوهش می‌باشد، ما در روش پیشنهادی خود با استفاده هم‌زمان از چند معیار متفاوت و اعمال برخی تغییرات در روشهای موجود، بهبود فرآیند مهاجرت بار را فراهم خواهیم کرد.\n\n'),
(37, 70, 2, 'افلاطون در کتاب جمهوری به سه عنصر در وجود انسان اشاره می کند عبارتند از قوه عقل یا استدلال و امیال  و احساسات  افلاطون شادی را حالتی از انسان می داند که بین این سه عنصرتعادل و هماهنگی وجود داشته باشد. اجزای شادکامی ومولفه های مربوط آن داراي سه جزء اصلي است: هيجان مثبت، رضايت از زندگي، فقدان هيجان منفي از قبيل افسردگي و اضطراب مايرز  و داينر (2003) نيز عقيده دارند كه شادكامي داراي مؤلفه هاي عاطفي، اجتماعي و شناختي است. مؤلفه عاطفي باعث ميشود كه فرد همواره از نظر خلقي شاد و خوشحال باشد. مؤلفه اجتماعي موجب گسترش روابط اجتماعي با ديگران و افزايش حمايت اجتماعي مي شود. مؤلفه شناختي موجب مي گردد فرد نوعي تفكر و پردازش اطلاعات ويژه خود داشته و وقايع روزمره را طوري تعبير و تفسير كند كه خوشبيني وي را به دنبال داشته باشد. روابط مثبت با ديگران، هدفمند بودن زندگي، رشد شخصي، دوست داشتن ديگران و طبيعت نيز از اجزاي شادكامي به حساب مي آيند  شادكامي پيامدهاي مثبتي برروي سبك زندگي و موفقيت تحصيلي دانشجويان دارد و ميل به انجام رفتارهايي كه با موفقيت تحصيلي مرتبط هستند را افزايش مي دهد. اين توضيح ريشه در مطالعات بسياري دارد كه بيان كرده اند شادكامي به فعاليتهاي جذاب و توليدكننده منجرمي شود. روانشناسان معتقدند میزان شادی در موفقیت تحصیلی دانشجویان عاملی مؤثر می باشد. بسیاری از تحقیقات نشان می دهد دانشجویانی که شادی دائمی و همیشگی دارند، از سلامت روانی بهتری برخوردارند.\n\n'),
(38, 50, 2, 'امروزه یکی از مرسوم‌ترین اقداماتی که جهت افزایش ظرفیت واحدهای شیرین سازی صورت می پذیرد، تغییر ساختاری فضای داخلی برج‌های جذب بوده که معمولا با جایگزینی بسترهای پرشده به این مهم پرداخته می شود. توسعه آکنه‌ها از سال 1950 با طراحی آکنه‌های نسل دوم پال و اینتالوکس آغاز گردید و بعد از آن با ایجاد آکنه‌های نسل سوم IMTP و CMR ادامه پیدا کرد. نسل جدید آکنه‌های به وجود آمده درسال‌های اخیر،  به دلیل نقش کلیدی که در فرایندهای جذب ودفع ایفا نموده‌اند،کاربرد بسیاری یافته‌اند. سوپر‌رینگ‌ها، رالو‌رینگ‌ها و رالو فلوها، چهارمین نسل آکنه‌ها هستندکه ویژگی‌های برجسته ای نسبت به سایر آکنه‌ها دارند. بیلت و اسکالتز ]1[ در سال 1999 به پیش‌بینی میزان انتقال جرم درون برج‌ها با استفاده از آکنه‌های منظم و نامنظم پرداختند. اسکالتز در سال 2003]2 [خصوصیات برخی از آکنه‌های نسل سوم از جمله ناتررینگ، CMR، IMTP و آکنه نسل چهارمی سوپررینگ را مورد بررسی قرار داد. داراکچیو و همکارانش ]3 [در سال 2005 به مقایسه آکنه‌های IMTP و رالوفلو پرداختند.\n\n'),
(39, 64, 2, 'بررسی و تعیین ظرفیتباربری پیها از مهمترین مباحث مهندسی ژئوتکنیک میباشد. پی یکی از  اصلیترین قسمتهای هر سازه محسوب میشود لذا بررسی و تعیین عملکرد آنها از اهمیت بالایی برخوردار است. انتقال بار سازه به خاک بدون آنکه باعث ایجاد گسیختگی برشی در خاک گردد، یا نشستهای بیش از اندازه ایجاد کند، از اصول طراحی پی به شمار میرود. از سوی دیگر جنبه های اقتصادی و توجیهپذیر بودن ساخت پی بایستی در نظر گرفته شود. روشهای مطلوب، از میان شیوههای متعدد، آنهایی است که ضمن برآورده کردن انتظارات ژئوتکنیکی از لحاظ اقتصادی نیز قابل توجیه باشند.با توجه به نیاز جامعه مبنی بر احداث سازههای بلند مرتبه (افزایش فشار وارده به خاک) و استفاده از زمین های ضعیف و نا مناسب (کاهش ظرفیتباربری) و همچنین احداث سازههای مجاور شیب، تحقیقات متعددی به منظور افزایش کارایی پیها انجام شده است. از جمله روشهای افزایش ظرفیتباربری میتوان به مسلح سازی خاک به وسیله لایههای ژئوگرید، اشاره نمود.در این مقاله با استفاده از روشهای عددی اثر لایههای ژئوگرید را بر افزایش ظرفیتباربری خاک در حالتی که در مجاورت شیب باشد را بررسی نموده. برای سازه های در مجاورت شیب که خاک زیر شالوده از جنس رس سست است ( مهمترین این سازهها پایه پلها میباشد)، دو مسأله اساسی وجود دارد یکی کاهش ظرفیتباربری به دلیل رس سست و همچنین کاهش پایداری به دلیل وجود شیب. اگر بخواهیم شیب را ملایمتر کنیم یا خاک رس را با خاک با مقاومت بالا جایگزین کنیم، هم زمان زیادی نیاز هست و هم هزینههای مربوطه بطور چشمگیری افزایش مییابد.\n\n'),
(40, 85, 2, 'استامینوفن دارویی  مسکن  و تب بر است که بطور شایع مورد استفاده قرار می گیرد  و اوردوز آن از مسمومیت های شایع  در مراجعین مراکز اورژانس بیمارستانی است که در مقادیر بالا باعث نکروز کبدی و کلیوی می گردد (1) .  در اواخر  سال 1960 میلادی  اولین مورد سمیت کبدی وابسته به مقدار بالای استامینوفن گزارش شد ، پس از آن در سال 1974 برای اولین بار ان استیل سیستئن بعنوان پادزهر موثر بر سمیت کبدی آن مورد استفاده  قرار گرفت و در سال 1977  ، perscatt  و همکاران  تزریق  وریدی  محلول  استریل 20% NAC را بشکلی موثر در درمان اوردوز استامینوفن  بکار بردند . این رژیم  شامل تزریق وریدی محلول رقیق شده ی ، 150 میلی گرم NAC به ازاء هر کیلوگرم وزن بیمار در مدت 15-30  دقیقه و  ادامه آن  با 50 میلی گرم به ازاء هر کیلوگرم در مدت 4 ساعت  و  100 میلی گرم به ازاء هر کیلوگرم وزن بدن در مدت 16 ساعت  و در صورت نیاز تکرار آخرین دوز تا سه نوبت و طبیعی شدن سطح آمینو ترانسفرازهای کبدی است ( 2و5).  در ایران نیز  بیش از دو دهه از تزریق وریدی محلول NAC در مراکز اورژانس مسمومین استفاده  می شود.\n\n'),
(41, 49, 2, 'در عصر فرا رقابتی، سازمانها با محیطی رو برو هستند که مشخصه آن افزایش پیچیدگی و جهانی شدن و پویایی است لذا سازمانها برای استمرار و استقرار خود با چالشهای نوینی مواجه ا ند که برون رفت از این چالش‌ها مستلزم توجه بیشتر به توسعه و تقویت مهارت‌ها و توانایی‌های درونی است که این کار از طریق مبانی دانش سازمانی و سرمایه فکری صورت می گیرد. \n\nسرمایه فکری یک دیدگاه کیفی را فراهم می کند که ارتباط نزدیکی با اندازه گیری و شناسایی دارایی های نامشهود  توسعه یافته توسط سازمان دارد(Claver, 2015) و در تحقیقات مختلف منابع نامشهود به عنوان منبعی قابل توجه برای خلق ارزش و دستیابی به مزیت رقابتی مطرح شده اند(Viktoria Goebel , 2015) و نشان داده شده  که سرمایه فکری بر عملکرد شرکت تأثیر دارد و به سازمان امکان تولید دارایی هایی که ارزش بیشتری دارند را می دهد( Chandra Sekhara,. Et al, 2015)).\n\nبا توجه به این مهم در بسیاری از مقالات سعی شده است، نقش استراتژیکی دارایی‌های نامحسوس، مانند استعداد کارکنان، ارزش‌های فرهنگی یا روابط طولانی مدت بین بنگاه اقتصادی و ودیعه گذاران آن مانند مشتریان، متحدان، فراهم کنندگان و جامعه به شکل کلی، در رقابت غیر قابل تحمل و سودآور را برجسته نمایند تا منجر به مدیریت کردن سرمایه فکری به عنوان یک موضوع کلیدی در دستورالجلسه مدیریتی شود(کاستارو و همکاران،2011).\n\n');
INSERT INTO `tests` (`id`, `study_field_id`, `language_id`, `text`) VALUES
(42, 51, 2, 'دومين گونه عمده گردشگري كه مي تواند در سطوح گردشگران خارجي، افرادزيادي را بر اساس گرايش هاي تقاضاي بازار به مقاصدگردشگري جذب كند، اكوتوريسم است. از سوي ديگر امروزه اكوتوريسم به عنوان مفهومي كه برپایه ایده هاي حفاظت محيط زيست و توسعة پايدار استوار است، رواج جهاني دارد . گرچه دخالت انسان در طبيعت باعث حذف بسياري از اكوسيستم ها كه نقش بيشتري نسبت به شرايط طبيعي نيز داشته اند شده است، اين نگراني براي طبيعت به واسطة گردشگري وجود دارد همچنان كه مك كرچر بر آسيب پذيري بدون پايداري تأكيد دارد. توسعه فناوري پيشرفت و صنعتي شدن امري است اجتنا ب ناپذير به نحوي كه اجتناب ناپذيري اين تغيير سريع موجب بروزپيامدهاي منفی عديده اي در بخش فرهنگي و اجتماعي واقتصادي و...شده است.بسترسازي شاخص هاي فرهنگي ،اجتماعي- اقتصادي جوامع مختلف به منظور همگامي و همسازي بعد معنوي و مادي نيازهاي انسان راهكار اين مشكل است و دراين ميان سازماندهي و برنامه ريزي محيط هاي طبيعي با كاربر يهاي عمومي ويژه و پيوند آن با جريان هاي گردشگري در شاخه هايي از آن به نام گردشگري طبيعت يكي از رهيافت هاي سازنده به شمار مي آيد.\n\n'),
(43, 63, 2, 'پديده جهاني شدن به گونه اي كه اكنون بسياري آن را تفسير مي نمايند ، نشان از تحولات و دگرگوني هاي گسترده اي در تاريخ دارد و همراه با آن ـ و به واسطه آن ـ شبكه به هم تنيده اي از روابط و نمادها شگل گرفته است كه مفاهيم پديده هاي كهن را دگرگون ساخته و به تبع طبيعت خويش ، نيازمند تعريف مجدد كه بر پاية درك و دريافت تازه اي از واقعيت ها و حقايق نوين باشد ، شكل گرفته است . واقعيت هايي كه در روند تكوين خود ، پديده هاي مزبور را تعريف نويني بنمايد .\n\nهمراه با دگرگوني گسترده و فراگيري كه جهاني شدن آن را در افكنده است ، دوره عمل به بسياري از باور داشت ها و دستاوردهايي كه طي لحظه هاي تاريخي پارينه هاي زمان رقم خورده است ، پايان خواهد يافت و آدمي ناچار خواهد شد در بسياري از دستاوردها و باور داشت هاي سياسي ، اقتصادي و فرهنگي خود كه از زمان شكل گيري نوين در سده هاي گذشته  با آنها خوي گرفته بود ، تجديد نظر نمايد . بر همين اساس ، فضاي فكري نويني  شكل خواهدگرفت كه درآن پرسش هاي تازه اي درباره تكوين اين پديده مطرح خواهد شد .\n\nدر مورد پديده جهاني شدن از سوي دانشمندان مختلف مطالعات زيادي انجام گرفته است . اما از ديدگاه جهاني شدن اقتصاد و تأثير آن بر سياست خارجي كشورها ، پژوهش هاي كمتري صورت گرفته است .\n\n'),
(44, 56, 2, 'تکثیر سنتی و مرسوم مورد از طریق قلمه‏های چوبی نرم درختان نابالغ و یا بذری که خواب فیزیولوژیک آن رفع شده است، انجام می‏گیرد . معمولا\" تکثیر با قلمه به دلیل سخت ریشه‏دار شدن این گیاه، بازدهی پایین و محدودیت گیاه مادری و تکثیر از طریق بذر به دلیل خواب بذور مشکل می‏باشد . توقف موقت رشد در هر ساختار گیاهی حاوی مریستم در اثر عوامل درونی و بیرونی را خواب گویند . خواب اوليه بذور را به دو گروه دروني و بيروني تقسيم می‌كنند. خواب بیرونی ناشی از پوسته غیرقابل نفوذ بذر به اکسیژن یا آب و یا به دلیل وجود مواد بازدارنده در اپیدرم یا غشا داخلی مجاور پوشش بذر می‌باشد . یکی از روشهای شکستن خواب ناشی از پوشش بذر، خراش‌دهی با اسید و غالبا\" اسید سولفوریک غلیظ است. گزارشات متعددی در زمینه مدت و غلظت تیمار اسیدی موثر در شکستن خواب بذر در انواع گونه‌های گیاهی وجود دارد. خراش‌دهی با اسید سولفوریک غلیظ به مدت 60 دقیقه یک تیمار موثر برای شروع جوانه‌زنی بذرهای Schizolobium amazollicum می‌باشد . خواب بذر گياه مريم نخودي ناشي از پوسته غيرقابل نفوذ بذر به آب و گازهاست. شاکری و همکاران (1388) مشاهده کردند که شکاف مکانیکی بذرهای گیاه مریم نخودی درصد جوانه‌زنی را 31 برابر و اسید سولفوریک غلیظ به مدت 15 دقیقه حدود 10 برابر نسبت به شاهد افزایش می‌دهد . بررسی تاثیر اسید جیبرلیک و اسید سولفوریک بر جوانه‌زنی Asparagus racemosus نشان داده است که در این گیاه اسید سولفوریک بیش از جیبرلیک اسید باعث افزایش جوانه‌زنی می‌شود . در چندین گونه Verticordia (از خانواده Myrtaceae) مشاهده شده است که حذف پوسته بذر (به منظور افزایش جذب آب) همراه با اضافه کردن هورمون جیبرلیک اسید (جهت رفع نیازهای پس از رسیدگی) سبب افزایش جوانه‌زنی بذور می‌شود .\n\n\n\n'),
(45, 92, 2, 'متاسفانه بسیاری از مشتاقان بر منابع ومیراث های فرهنگی وهنری گذشته دسترسی ندارند. کوشش ما براین است تاضمن معرفی هنر گل ومرغ که نزد ایرانیان بخصوص نسل جوان، ناشناخته مانده است، مجموعه ای از روش ها ی(تکنیک ها ) نقاشی وهنری که از عشق مذهبی و میهنی هنرمندان ایرانی سرچشمه می گیرد نیز مطرح  شود.هدف از نوشتن این پایان نامه معرفی نقش گل ومرغ وآشنایی با هنر گل ومرغ سازی  و تحلیل وبررسی این هنر ناب وارزشمند در دوران های مختلف ومعاصر هنر ایران  است.در این تحقیق به بررسی ویزگی های نقاشی ایرانی ، جایگاه خیال وتصور ، خلق آثار که همزمان نشان دهنده ی نگاه هنرمند ایرانی و پیوستگی و استمرار فرهنگی و نشان دهنده ی سیر تحول نقوش گل ومرغ در ادوار تاریخی مختلف است می پردازیم.\n\n'),
(46, 53, 2, 'سیستم سکان، مسؤولیت مهمی را در هدایت و کنترل شناورها و کشتی ها به عهده دارد. این امر در زمان دور زدن و یا انحراف شناور میتواند حساسیت بیشتری در زمان طراحی داشته باشد. امروزه با توجه به پیشرفت چشمگیری که در زمینه معماری کشتی و علم سیالات به وجود آمده، در دو بخش قابل تأمل است: طراحی هیدرودینامیکی بال (تیغه) سکان و طراحی سیستم فرمان و تجهیزات انتقال دستور به مکانیزم بال شناور.\n\nدر این تحقیق که به صورت عملی و آزمایشگاهی بر روی شناور 800 تنی اجرا گردید، از هیدروموتور پیستونی در محل سکان شناور استفاده گردیده و نتایج به دست آمده مورد تحلیل قرار گرفته است. نتایج نشان گر این است که با نصب پمپ روتوری هیدروموتور بر روی سکان، کنترل تیغه شناور برای فرمان دادن از توان 165.6 تا 13800 کیلووات قابل اعمال است که بیانگر محدودهی وسیعی از اعمال نیرو بر تیغه سکان میباشد. \n\n\n\n'),
(47, 74, 2, 'در سراسر دور اروگوئه ، گروه موسوم به کايزنر ، يعني گروهي متشکل از کشورهاي غيريارانه ده صادرکننده محصولات کشاورزي به رهبري استرالياکه هم کشورهاي توسعه يافته وهم کشورهاي درحال توسعه در ان حضور داشتند، درصدد از بين بردن همه يا تقريباً همه انواع يارانه ها به صادرات کشاورزي بودند. مذاکرات دور اروگوئه ، مبنايي حقوقي براي اعطاي يارانه هاي صادارتي به کالاهاي کشاورزي را فراهم کرد ؛ اين درحالي است که اعطاي اين يارانه ها براي صادرات محصولات توليدي  ممنوع است. اين امکان با دو شرط ديگر همراه است : نخست اينکه يارانه ها را نمي توان براي صادراتي اعطا کرد که پيش از پايان دور اروگوئه از هيچ يارانه اي برخوردار نبوده اند ؛ دوم اينکه کشورها بايد محدوده هاي کمي مربوط به هر کشور و هرگروه کالايي را بطور دقيق به دبيرخانه سازمان تجارت جهاني اعلام کنند.\n\nدر دور اروگوئه ايالات متحده آمريکا هم خواستار لغو مرحله اي تمام يارانه هاي صادراتي به کشاورزي بودولي                 Croome , OP.Cit , P.294 – 296)) تلاش براي از بين بردن يا محدودکردن شديد يارانه هاي صادراتي به جايي نرسيد ، چرا که جامعه اروپا عميقاً به يارانه هاي صادراتي بعنوان راهي   براي خلاصي از مازاد ايجاد شده با خط مشي مشترک کشاورزي پاي بند بود.\n\n'),
(48, 88, 2, 'هدف اصلی این پژوهش بررسی عوامل موثر در استقرار اظهارنامه الکترونیکی در اداره امور مالیاتی و اقتصادی خراسان رضوی حوزه مشهد مي باشد. این پژوهش با توجه به ماهیت آن توصیفی و از لحاظ روش کاربردی  بوده و نمونه آمار ی آن به صورت  نمونه گیری تصادفی ساده مشتمل از 91 نفر از اشخاص حقوقی دارای امضای الکترونیکی ، با استفاده از پرسشنامه محقق ساخته مورد مطالعه قرار گرفت. جهت ارزيابي و بررسي فرضيههای تحقیق که شامل یک فرضیه اصلی و چهار فرضیه فرعی است. در آمار توصيفي از جداول فراواني و درصد‌ها، ميانگين‌ها و انحراف معيارها و  همچنين آزمون tی یک نمونه ای برای پاسخ فرضیه های تحقيق استفاده شد. ضمناٌ براي انجام محاسبات از بسته نرم افزاري Ver16.0 (Spss/pc++) استفاده  شده است.نتايج پژوهش نشان داد به ترتيب چهار فرضيه انتظار کارایی ،انتظار تلاش ،تاثیر اجتماعی و شرایط پشتیبانی کننده تایید شده و در استقرار اظهارنامه الکترونیکی تاثیر دارند. پیشنهاد ها شامل ایجاد مشوق ها توسط سازمان مالیاتی و استقرار اقدامات اصلاحی یا پیشگیرانه ی سازمان در استفاده از اظهارنامه ی الکترونیکی است. \n\n\n\n'),
(49, 86, 2, 'در پروژه ی پیش رو، یک آشکارساز نوری مادون قرمز نزدیک ارائه، طراحی و آنالیز شده است. آشکارساز طراحی شده برای کار در محدوده ی طول موج 1.5 تا 1.55 میکرومتر و برای کاربردهای مخابرات نوری مناسب است. در این پروژه، ابتدا ساختار، توابع موج و توابع دی الکتریک نقاط کوانتومی با اندازه های متفاوت را بررسی می نماییم و در ادامه ساختاری برای آشکارساز مادون قرمز بر مبنای استفاده از دو لایه نقطه ی کوانتومی و یک صفحه ی پلاسمونی ارائه می دهیم. استفاده از صفحه ی پلاسمونی در ساختار آشکارساز، باعث افزایش قابل توجه توان جذب، بازده کوانتومی و چگالی جریان نوری می شود. اندازه ی نقاط کوانتومی، مقدار FWHM، جنس، ضخامت و فاصله ی صفحه ی پلاسمونی تا لایه ی نقطه ی کوانتومی پارامترهای تاثیرگذار بر عملکرد آشکارساز می باشند که هر کدام مورد بررسی و تحلیل قرار گرفته اند. در انتها ساختاری بهینه برای آشکارساز طراحی شده معرفی می گردد.  \n\n\n\n');

-- --------------------------------------------------------

--
-- Table structure for table `Tickets`
--

CREATE TABLE `Tickets` (
  `ticket_number` varchar(6) NOT NULL,
  `creator_id` int(11) NOT NULL,
  `user_type` tinyint(1) NOT NULL,
  `subject` varchar(50) NOT NULL,
  `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `create_date_persian` varchar(16) NOT NULL,
  `update_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_date_persian` varchar(16) NOT NULL,
  `state` varchar(8) NOT NULL DEFAULT 'waiting'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `Tickets`
--

INSERT INTO `Tickets` (`ticket_number`, `creator_id`, `user_type`, `subject`, `create_date`, `create_date_persian`, `update_date`, `update_date_persian`, `state`) VALUES
('099ce7', 2, 1, 'تست میشه', '2019-02-24 09:56:22', '1397/12/5 13:26', '2019-03-20 11:53:11', '1397/12/29 15:23', 'waiting'),
('10c8c2', 2, 1, 'تست میشه', '2019-02-24 09:59:31', '1397/12/5 13:29', '2019-03-20 11:11:05', '1397/12/29 14:41', 'answered'),
('16b952', 2, 1, 'لاگین نشدن به کنسول4', '2019-02-24 20:03:21', '1397/12/5 23:33', '2019-02-24 20:03:21', '1397/12/5 23:33', 'waiting'),
('1c8281', 2, 1, 'لاگین نشدن به کنسول', '2019-02-24 10:00:21', '1397/12/5 13:30', '2019-02-24 10:00:21', '1397/12/5 13:30', 'waiting'),
('407dcc', 1, 2, 'تست میشه', '2019-03-05 19:58:19', '1397/12/14 23:28', '2019-03-20 12:05:05', '1397/12/29 15:35', 'waiting'),
('58a118', 1, 2, 'تست میشه', '2019-03-05 19:55:56', '1397/12/14 23:25', '2019-03-05 19:55:56', '1397/12/14 23:25', 'waiting'),
('5a981a', 2, 1, 'لاگین نشدن به کنسول6,mnloll', '2019-02-24 20:04:45', '1397/12/5 23:34', '2019-03-20 16:19:00', '1397/12/29 19:49', 'answered'),
('77ad1c', 2, 1, 'تمدید دامنه', '2019-03-02 17:15:48', '1397/12/11 20:45', '2019-03-20 16:17:13', '1397/12/29 19:47', 'answered'),
('8c7513', 2, 1, 'لاگین نشدن به کنسول2', '2019-02-24 19:59:51', '1397/12/5 23:29', '2019-02-24 19:59:51', '1397/12/5 23:29', 'waiting'),
('90e344', 2, 1, 'تست میشه', '2019-02-24 12:56:45', '1397/12/5 16:26', '2019-02-24 12:56:45', '1397/12/5 16:26', 'waiting'),
('9290cc', 2, 1, 'تمدید دامنه566698', '2019-02-24 20:05:45', '1397/12/5 23:35', '2019-02-28 10:37:47', '1397/12/9 14:07', 'read'),
('9c6755', 2, 1, 'تمدید دامنه', '2019-02-24 10:19:48', '1397/12/5 13:49', '2019-02-24 10:19:48', '1397/12/5 13:49', 'waiting'),
('9d94b7', 1, 2, 'لاگین نشدن به کنسول', '2019-03-05 19:57:23', '1397/12/14 23:27', '2019-03-05 19:57:23', '1397/12/14 23:27', 'waiting'),
('a6d316', 2, 1, 'لاگین نشدن به کنسول3', '2019-02-24 20:02:33', '1397/12/5 23:32', '2019-02-24 20:02:33', '1397/12/5 23:32', 'waiting'),
('ad10b2', 2, 1, 'تست میشه', '2019-02-24 09:54:21', '1397/12/5 13:24', '2019-02-24 09:54:21', '1397/12/5 13:24', 'waiting'),
('bfd311', 2, 1, 'تمدید دامنه', '2019-03-05 19:59:54', '1397/12/14 23:29', '2019-03-20 16:13:28', '1397/12/29 19:43', 'answered'),
('eda4cc', 2, 1, 'لاگین نشدن به کنسول5', '2019-02-24 20:04:26', '1397/12/5 23:34', '2019-03-20 16:21:38', '1397/12/29 19:51', 'answered');

-- --------------------------------------------------------

--
-- Table structure for table `Ticket_Messages`
--

CREATE TABLE `Ticket_Messages` (
  `ticket_id` int(11) NOT NULL,
  `ticket_number` varchar(6) CHARACTER SET utf8 NOT NULL,
  `parent_ticket_id` varchar(6) DEFAULT '0',
  `sender_id` int(11) NOT NULL,
  `sent_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `sent_date_persian` varchar(16) DEFAULT NULL,
  `body` text NOT NULL,
  `attach_files` varchar(250) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `Ticket_Messages`
--

INSERT INTO `Ticket_Messages` (`ticket_id`, `ticket_number`, `parent_ticket_id`, `sender_id`, `sent_date`, `sent_date_persian`, `body`, `attach_files`) VALUES
(1, 'ad10b2', '0', 2, '2019-02-24 09:54:21', '1397/12/5 13:24', '<p>,jvbsdmhs</p>', NULL),
(2, '099ce7', '0', 2, '2019-02-24 09:56:22', '1397/12/5 13:26', '<p>,bbk,hsgfhvn,f hna</p>', NULL),
(3, '10c8c2', '0', 2, '2019-02-24 09:59:31', '1397/12/5 13:29', '<p>ksbcvdhms</p>', NULL),
(4, '1c8281', '0', 2, '2019-02-24 10:00:21', '1397/12/5 13:30', '<p>,nbdvm vh,d</p>', NULL),
(5, '9c6755', '0', 2, '2019-02-24 10:19:48', '1397/12/5 13:49', '<h2>djdsmfvd</h2>', NULL),
(6, '90e344', '0', 2, '2019-02-24 12:56:45', '1397/12/5 16:26', '<h2><b>x,dnv</b>fb,v</h2>', NULL),
(7, '8c7513', '0', 2, '2019-02-24 19:59:51', '1397/12/5 23:29', '<p>lcr<a href=\"http://google.com\">norvnk</a>fevlj,nfl,fnvofl</p>', NULL),
(8, 'a6d316', '0', 2, '2019-02-24 20:02:33', '1397/12/5 23:32', '<p>l sjfnvjrnvlrjk</p>', NULL),
(9, '16b952', '0', 2, '2019-02-24 20:03:21', '1397/12/5 23:33', '<p>lerwfjro</p>', NULL),
(10, 'eda4cc', '0', 2, '2019-02-24 20:04:26', '1397/12/5 23:34', '<p>lerwfjroolnil</p>', NULL),
(11, '5a981a', '0', 2, '2019-02-24 20:04:45', '1397/12/5 23:34', '<p>lerwfjroolnillferfjrjl</p>', NULL),
(12, '9290cc', '0', 2, '2019-02-24 20:05:45', '1397/12/5 23:35', '<p>.wenfl</p>', NULL),
(17, '9290cc', '12', 0, '2019-02-27 19:18:37', '1397/12/08 22:50', 'تصبارتمقنرمتوثقدردتمث', NULL),
(24, '9290cc', '17', 2, '2019-02-28 10:37:47', '1397/12/9 14:07', '<p>ljknld,vbfkg,dvsdsk,</p>', NULL),
(25, '77ad1c', '0', 2, '2019-03-02 17:15:48', '1397/12/11 20:45', '<p>.jh,mhbvgh</p>', NULL),
(26, '492da9', '0', 1, '2019-03-05 18:30:30', '1397/12/14 22:00', '<p>kjlfvbrkj,vbeefhvhew fhvkwefbvhk,</p>', NULL),
(27, '4f3e85', '0', 1, '2019-03-05 18:42:16', '1397/12/14 22:12', '<p>;<b>ksjwvo</b>ljjlcwre,jc</p>', NULL),
(28, 'db228b', '0', 1, '2019-03-05 18:43:07', '1397/12/14 22:13', '<p>jfbvewrhvbehrkcbweriucmewr</p>', NULL),
(29, '360c2c', '0', 1, '2019-03-05 18:44:43', '1397/12/14 22:14', '<p>nfmkn fdmb nkfdn d,c</p>', NULL),
(30, 'f353b8', '0', 1, '2019-03-05 19:48:33', '1397/12/14 23:18', '<p>, dsnvjfbvhbn hckhds chsn hdkhm cj</p>', NULL),
(31, '7bdd14', '0', 1, '2019-03-05 19:55:05', '1397/12/14 23:25', '<p>khwbvikhvbyeifjvgijfbcjiefhci</p>', NULL),
(32, '58a118', '0', 1, '2019-03-05 19:55:56', '1397/12/14 23:25', '<p>efhvirlvhtbvlth</p>', NULL),
(33, '9d94b7', '0', 1, '2019-03-05 19:57:23', '1397/12/14 23:27', '<p>&nbsp;d kfbifkb ihk bhifkh bhif</p>', NULL),
(34, '407dcc', '0', 1, '2019-03-05 19:58:19', '1397/12/14 23:28', '<p>jldfkn oefjhh fjck</p>', NULL),
(35, 'bfd311', '0', 2, '2019-03-05 19:59:54', '1397/12/14 23:29', '<p>nd bhld bhfdk</p>', NULL),
(36, '099ce7', '2', 0, '2019-03-20 11:21:53', '1397/12/29 14:51', 'تست پاسخ', NULL),
(37, '099ce7', '36', 2, '2019-03-20 11:45:44', '1397/12/29 15:15', '<p>تست پاسخ کاربر</p>', NULL),
(38, '099ce7', '2', 0, '2019-03-20 11:52:52', '1397/12/29 15:22', 'جواب سوال کاربر', NULL),
(39, '099ce7', '38', 2, '2019-03-20 11:53:11', '1397/12/29 15:23', '<p>شت</p>', NULL),
(40, '407dcc', '34', 0, '2019-03-20 11:55:22', '1397/12/29 15:25', 'فارسی بنویس :)', NULL),
(41, '407dcc', '40', 1, '2019-03-20 11:55:55', '1397/12/29 15:25', '<p>باشه ://</p>', NULL),
(42, '407dcc', '34', 0, '2019-03-20 11:59:07', '1397/12/29 15:29', 'خب چیکار داشتی؟', NULL),
(43, '407dcc', '42', 1, '2019-03-20 12:00:08', '1397/12/29 15:30', '<p>کی پنل ادمین تموم میشه ؟</p>', NULL),
(44, '407dcc', '34', 0, '2019-03-20 12:02:10', '1397/12/29 15:32', 'نمیدونم :(', NULL),
(45, '407dcc', '44', 1, '2019-03-20 12:02:45', '1397/12/29 15:32', '<p>سریع باش&nbsp;</p>', NULL),
(46, '407dcc', '34', 0, '2019-03-20 12:04:30', '1397/12/29 15:34', 'باشه', NULL),
(47, '407dcc', '46', 1, '2019-03-20 12:05:05', '1397/12/29 15:35', '<p>خدا کنه ارور حل بشه</p>', NULL),
(48, 'bfd311', '35', 0, '2019-03-20 16:13:28', '1397/12/29 19:43', '<p>تست در صفحه تیکت ها</p>', NULL),
(49, '77ad1c', '25', 0, '2019-03-20 16:17:13', '1397/12/29 19:47', '<p>تست پاسخ</p>', NULL),
(50, '5a981a', '11', 0, '2019-03-20 16:19:00', '1397/12/29 19:49', '<p>خطایابی</p>', NULL),
(51, 'eda4cc', '10', 0, '2019-03-20 16:21:38', '1397/12/29 19:51', '<p>حام بابا</p>', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `translators`
--

CREATE TABLE `translators` (
  `translator_id` int(11) NOT NULL,
  `username` varchar(30) NOT NULL,
  `password` varchar(32) NOT NULL,
  `fname` varchar(50) NOT NULL,
  `lname` varchar(50) NOT NULL,
  `sex` tinyint(1) NOT NULL DEFAULT '1',
  `email` varchar(256) NOT NULL,
  `cell_phone` varchar(11) NOT NULL,
  `phone` varchar(11) DEFAULT NULL,
  `meli_code` varchar(10) NOT NULL,
  `melicard_photo` varchar(20) NOT NULL,
  `avatar` varchar(20) NOT NULL,
  `degree` varchar(20) NOT NULL,
  `exp_years` varchar(2) DEFAULT NULL,
  `address` text,
  `register_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `register_date_persian` varchar(16) DEFAULT NULL,
  `en_to_fa` tinyint(1) DEFAULT '0',
  `fa_to_en` tinyint(1) DEFAULT '0',
  `level` tinyint(1) DEFAULT '2',
  `is_active` tinyint(1) DEFAULT '0',
  `is_employed` tinyint(1) DEFAULT '0',
  `is_denied` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `translators`
--

INSERT INTO `translators` (`translator_id`, `username`, `password`, `fname`, `lname`, `sex`, `email`, `cell_phone`, `phone`, `meli_code`, `melicard_photo`, `avatar`, `degree`, `exp_years`, `address`, `register_date`, `register_date_persian`, `en_to_fa`, `fa_to_en`, `level`, `is_active`, `is_employed`, `is_denied`) VALUES
(1, 'coderguy', '81d93b8220c41d7a3b911024ac34464c', 'مهدی', 'گندمی', 1, 'coderguy1999@gmail.com', '09389318493', '05632313094', '0640617743', 'card.jpg', 'default-avatar.svg', 'کارشناسی', '2', 'بیرجند', NULL, '1397/10/30 19:52', 1, 1, 2, 1, 1, 0),
(5, 'coderguy1985', '81d93b8220c41d7a3b911024ac34464c', 'مهدی', 'گندمی', 1, 'coderguy1985@gmail.com', '09389318493', '05632313094', '0640617743', '', 'default-avatar.svg', 'کاردانی', '2', '', '2019-02-11 20:37:48', '1397/11/23 00:07', 0, 1, 2, 0, 1, 0),
(7, 'mehdi', '81d93b8220c41d7a3b911024ac34464c', 'مهدی', 'گندمی', 1, 'watch.dogs3030@gmail.com', '09389318493', '05632313094', '0640617743', 'card.jpg', '80a2c20bbf6e8527.png', 'کاردانی', '2', 'jskbvhefjvdf', '2019-03-10 18:39:03', '1397/12/19 22:09', 1, 0, 2, 1, 0, 0),
(8, 'admin', '2a6d1ba9c08ccb0cc7cabe1bf0c8258f', 'رضا', 'قاسمی', 1, 'mehdigandomi.contact@gmail.com', '09389318493', '09389318493', '0640617744', 'card.jpg', 'default-avatar.svg', 'کارشناسی', '10', 'تهران', '2019-03-15 20:54:52', '1397/12/24 19:52', 1, 1, 1, 1, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `translator_account`
--

CREATE TABLE `translator_account` (
  `id` int(11) NOT NULL,
  `translator_id` int(11) NOT NULL,
  `card_number` varchar(16) DEFAULT NULL,
  `shaba_number` varchar(24) DEFAULT NULL,
  `bank_name` varchar(20) DEFAULT NULL,
  `account_owner` varchar(50) DEFAULT NULL,
  `account_credit` varchar(10) DEFAULT NULL,
  `revenue` varchar(10) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `translator_account`
--

INSERT INTO `translator_account` (`id`, `translator_id`, `card_number`, `shaba_number`, `bank_name`, `account_owner`, `account_credit`, `revenue`) VALUES
(1, 1, '6037997144942727', '126569589589652896258965', 'ملی', 'مهدی گندمی', '1000000', '10000000'),
(2, 8, '6037997444942782', '126529659589258925892558', 'ملی', 'رضا قاسمی', '10000000', '100000000');

-- --------------------------------------------------------

--
-- Table structure for table `translator_checkout_request`
--

CREATE TABLE `translator_checkout_request` (
  `id` int(11) NOT NULL,
  `translator_id` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `request_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `request_date_persian` varchar(16) NOT NULL,
  `is_paid` tinyint(1) NOT NULL DEFAULT '0',
  `state` tinyint(1) NOT NULL DEFAULT '-1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `translator_checkout_request`
--

INSERT INTO `translator_checkout_request` (`id`, `translator_id`, `amount`, `request_date`, `request_date_persian`, `is_paid`, `state`) VALUES
(16, 1, 500000, '2019-03-05 17:50:57', '1397/12/14 21:20', 0, -1),
(17, 1, 500000, '2019-03-05 18:05:47', '1397/12/14 21:35', 0, -1);

-- --------------------------------------------------------

--
-- Table structure for table `translator_order_request`
--

CREATE TABLE `translator_order_request` (
  `id` int(11) NOT NULL,
  `translator_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `request_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `request_date_persian` varchar(16) NOT NULL,
  `is_denied` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `translator_order_request`
--

INSERT INTO `translator_order_request` (`id`, `translator_id`, `order_id`, `request_date`, `request_date_persian`, `is_denied`) VALUES
(1, 1, 22, '2019-03-20 12:08:18', '1397/12/29 15:38', 0);

-- --------------------------------------------------------

--
-- Table structure for table `translator_tests`
--

CREATE TABLE `translator_tests` (
  `id` int(11) NOT NULL,
  `test_id` int(11) NOT NULL,
  `translator_id` int(11) NOT NULL,
  `translated_text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `translator_tests`
--

INSERT INTO `translator_tests` (`id`, `test_id`, `translator_id`, `translated_text`) VALUES
(1, 18, 7, 'زذباربللرعنایباریبرایبرهیبذپبت گب\nذذببذپبدذمبوتر بر\nبربذبورذبنار');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(30) NOT NULL,
  `password` varchar(32) NOT NULL,
  `fname` varchar(50) NOT NULL,
  `lname` varchar(50) NOT NULL,
  `email` varchar(256) NOT NULL,
  `phone` varchar(11) DEFAULT NULL,
  `avatar` varchar(20) DEFAULT 'default-avatar.svg',
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `register_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `register_date_persian` varchar(16) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `fname`, `lname`, `email`, `phone`, `avatar`, `is_active`, `register_date`, `register_date_persian`) VALUES
(2, 'coderguy', '81d93b8220c41d7a3b911024ac34464c', 'مهدی', 'گندمی', 'coderguy1999@gmail.com', '09389318493', '898905b65ed4a096.jpg', 1, '2019-01-20 16:22:21', '1397/10/30 19:52'),
(8, 'coderguy1999', '81d93b8220c41d7a3b911024ac34464c', 'مهدی', 'گندمی', 'coderguy1998@gmail.com', '05632313094', 'default-avatar.svg', 1, '2019-01-24 09:52:48', '1397/11/4 13:22'),
(12, 'mehdi', '81d93b8220c41d7a3b911024ac34464c', 'مهدی', 'گندمی', 'watch.dogs3030@gmail.com', '09389318493', 'default-avatar.svg', 1, '2019-02-06 12:03:49', '1397/11/17 15:33');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `forgot_password`
--
ALTER TABLE `forgot_password`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notif_id`);

--
-- Indexes for table `notif_translator`
--
ALTER TABLE `notif_translator`
  ADD KEY `translator_id` (`translator_id`),
  ADD KEY `notif_id` (`notif_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `orders_ibfk_1` (`orderer_id`);

--
-- Indexes for table `order_logs`
--
ALTER TABLE `order_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `payment_logs`
--
ALTER TABLE `payment_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `translator_id` (`translator_id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `study_fields`
--
ALTER TABLE `study_fields`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tests`
--
ALTER TABLE `tests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tests_ibfk_1` (`study_field_id`);

--
-- Indexes for table `Tickets`
--
ALTER TABLE `Tickets`
  ADD PRIMARY KEY (`ticket_number`);

--
-- Indexes for table `Ticket_Messages`
--
ALTER TABLE `Ticket_Messages`
  ADD PRIMARY KEY (`ticket_id`);

--
-- Indexes for table `translators`
--
ALTER TABLE `translators`
  ADD PRIMARY KEY (`translator_id`);

--
-- Indexes for table `translator_account`
--
ALTER TABLE `translator_account`
  ADD PRIMARY KEY (`id`),
  ADD KEY `translator_id` (`translator_id`);

--
-- Indexes for table `translator_checkout_request`
--
ALTER TABLE `translator_checkout_request`
  ADD PRIMARY KEY (`id`),
  ADD KEY `translator_id` (`translator_id`);

--
-- Indexes for table `translator_order_request`
--
ALTER TABLE `translator_order_request`
  ADD PRIMARY KEY (`id`),
  ADD KEY `translator_id` (`translator_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `translator_tests`
--
ALTER TABLE `translator_tests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `test_id` (`test_id`),
  ADD KEY `translator_id` (`translator_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `forgot_password`
--
ALTER TABLE `forgot_password`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notif_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;
--
-- AUTO_INCREMENT for table `order_logs`
--
ALTER TABLE `order_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `payment_logs`
--
ALTER TABLE `payment_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `tests`
--
ALTER TABLE `tests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;
--
-- AUTO_INCREMENT for table `Ticket_Messages`
--
ALTER TABLE `Ticket_Messages`
  MODIFY `ticket_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;
--
-- AUTO_INCREMENT for table `translators`
--
ALTER TABLE `translators`
  MODIFY `translator_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
--
-- AUTO_INCREMENT for table `translator_account`
--
ALTER TABLE `translator_account`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `translator_checkout_request`
--
ALTER TABLE `translator_checkout_request`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;
--
-- AUTO_INCREMENT for table `translator_order_request`
--
ALTER TABLE `translator_order_request`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `translator_tests`
--
ALTER TABLE `translator_tests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `notif_translator`
--
ALTER TABLE `notif_translator`
  ADD CONSTRAINT `notif_translator_ibfk_1` FOREIGN KEY (`translator_id`) REFERENCES `translators` (`translator_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `notif_translator_ibfk_2` FOREIGN KEY (`notif_id`) REFERENCES `notifications` (`notif_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`orderer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `order_logs`
--
ALTER TABLE `order_logs`
  ADD CONSTRAINT `order_logs_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `payment_logs`
--
ALTER TABLE `payment_logs`
  ADD CONSTRAINT `payment_logs_ibfk_1` FOREIGN KEY (`translator_id`) REFERENCES `translators` (`translator_id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Constraints for table `tests`
--
ALTER TABLE `tests`
  ADD CONSTRAINT `tests_ibfk_1` FOREIGN KEY (`study_field_id`) REFERENCES `study_fields` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Constraints for table `translator_account`
--
ALTER TABLE `translator_account`
  ADD CONSTRAINT `translator_account_ibfk_1` FOREIGN KEY (`translator_id`) REFERENCES `translators` (`translator_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `translator_checkout_request`
--
ALTER TABLE `translator_checkout_request`
  ADD CONSTRAINT `translator_checkout_request_ibfk_1` FOREIGN KEY (`translator_id`) REFERENCES `translators` (`translator_id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Constraints for table `translator_order_request`
--
ALTER TABLE `translator_order_request`
  ADD CONSTRAINT `translator_order_request_ibfk_1` FOREIGN KEY (`translator_id`) REFERENCES `translators` (`translator_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `translator_order_request_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `translator_tests`
--
ALTER TABLE `translator_tests`
  ADD CONSTRAINT `translator_tests_ibfk_1` FOREIGN KEY (`test_id`) REFERENCES `tests` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `translator_tests_ibfk_2` FOREIGN KEY (`translator_id`) REFERENCES `translators` (`translator_id`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
