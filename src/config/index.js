// PENDA Configuration
export const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

export const CONTINENTS = [
  'Africa',
  'Europe',
  'North America',
  'South America',
  'Asia',
  'Australia/Oceania',
];

export const COUNTRIES_BY_CONTINENT = {
  Africa: [
    'Algeria','Angola','Benin','Botswana','Burkina Faso','Burundi','Cabo Verde',
    'Cameroon','Central African Republic','Chad','Comoros','Congo','Côte d\'Ivoire',
    'Democratic Republic of the Congo','Djibouti','Egypt','Equatorial Guinea',
    'Eritrea','Eswatini','Ethiopia','Gabon','Gambia','Ghana','Guinea',
    'Guinea-Bissau','Kenya','Lesotho','Liberia','Libya','Madagascar','Malawi',
    'Mali','Mauritania','Mauritius','Morocco','Mozambique','Namibia','Niger',
    'Nigeria','Rwanda','São Tomé and Príncipe','Senegal','Seychelles',
    'Sierra Leone','Somalia','South Africa','South Sudan','Sudan','Tanzania',
    'Togo','Tunisia','Uganda','Zambia','Zimbabwe',
  ],
  Europe: [
    'Albania','Andorra','Austria','Belarus','Belgium','Bosnia and Herzegovina',
    'Bulgaria','Croatia','Cyprus','Czech Republic','Denmark','Estonia','Finland',
    'France','Germany','Greece','Hungary','Iceland','Ireland','Italy','Kosovo',
    'Latvia','Liechtenstein','Lithuania','Luxembourg','Malta','Moldova',
    'Monaco','Montenegro','Netherlands','North Macedonia','Norway','Poland',
    'Portugal','Romania','Russia','San Marino','Serbia','Slovakia','Slovenia',
    'Spain','Sweden','Switzerland','Turkey','Ukraine','United Kingdom',
  ],
  'North America': [
    'Antigua and Barbuda','Bahamas','Barbados','Belize','Canada','Costa Rica',
    'Cuba','Dominica','Dominican Republic','El Salvador','Grenada','Guatemala',
    'Haiti','Honduras','Jamaica','Mexico','Nicaragua','Panama',
    'Saint Kitts and Nevis','Saint Lucia','Saint Vincent and the Grenadines',
    'Trinidad and Tobago','United States',
  ],
  'South America': [
    'Argentina','Bolivia','Brazil','Chile','Colombia','Ecuador','Guyana',
    'Paraguay','Peru','Suriname','Uruguay','Venezuela',
  ],
  Asia: [
    'Afghanistan','Armenia','Azerbaijan','Bahrain','Bangladesh','Bhutan',
    'Brunei','Cambodia','China','Georgia','India','Indonesia','Iran','Iraq',
    'Israel','Japan','Jordan','Kazakhstan','Kuwait','Kyrgyzstan','Laos',
    'Lebanon','Malaysia','Maldives','Mongolia','Myanmar','Nepal','North Korea',
    'Oman','Pakistan','Palestine','Philippines','Qatar','Saudi Arabia',
    'Singapore','South Korea','Sri Lanka','Syria','Tajikistan','Thailand',
    'Timor-Leste','Turkmenistan','United Arab Emirates','Uzbekistan','Vietnam',
    'Yemen',
  ],
  'Australia/Oceania': [
    'Australia','Fiji','Kiribati','Marshall Islands','Micronesia','Nauru',
    'New Zealand','Palau','Papua New Guinea','Samoa','Solomon Islands',
    'Tonga','Tuvalu','Vanuatu',
  ],
};

export const ICEBREAKER_QUESTIONS = [
  'What makes you laugh?',
  'Where would you travel tomorrow?',
  'What\'s your passion?',
  'What song is stuck in your head right now?',
  'What\'s the best meal you\'ve ever had?',
  'What does a perfect Sunday look like for you?',
  'What are you most grateful for?',
  'If you could learn any skill instantly, what would it be?',
];

export const GENDER_OPTIONS = ['Male', 'Female', 'Non-binary', 'Prefer not to say'];

export const USER_TYPES = {
  NORMAL: 'normal',
  WABABA: 'wababa',
  WAMAMA: 'wamama',
};
