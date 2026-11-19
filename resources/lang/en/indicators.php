<?php

declare(strict_types=1);

// Why a domain is blocked. The first five are set by the analysis of the domain, the others are chosen by hand.
return [
    'non_ascii'           => 'Non-ASCII characters',
    'cyrillic_characters' => 'Cyrillic characters (possible lookalike of a Latin domain)',
    'greek_characters'    => 'Greek characters (possible lookalike of a Latin domain)',
    'mixed_scripts'       => 'Several alphabets mixed in one label',
    'punycode'            => 'Punycode (encoded international domain)',
    'phishing'            => 'Phishing',
    'spam'                => 'Spam',
    'disposable'          => 'Disposable address provider',
    'lookalike'           => 'Lookalike of a legitimate domain',
];
