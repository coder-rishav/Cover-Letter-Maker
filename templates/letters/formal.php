<?php

declare(strict_types=1);

/**
 * Formal tone: measured, traditional business correspondence.
 *
 * Available placeholders: {company}, {role}, {hiring_manager}, {name},
 * {years_phrase}, {duration_phrase}, {skills_phrase}, {title_phrase},
 * {recent_company_phrase}, {achievement_sentence}.
 */
return [
    'openings' => [
        'I am writing to express my strong interest in the {role} position at {company}. With {years_phrase} in software engineering, I have built a career around solving meaningful problems with clean, reliable systems, and I would welcome the opportunity to bring that focus to your team.',
        'Please accept this letter as my formal application for the {role} role at {company}. As a {title_phrase} with {years_phrase}, I offer a disciplined approach to delivery and a genuine commitment to the quality of the work I ship.',
        'It is with considerable pleasure that I submit my application for the {role} position at {company}. My background as a {title_phrase} has prepared me to contribute quickly, and I am confident that my experience aligns with the standards your team upholds.',
        'I am pleased to apply for the {role} opening at {company}. Over the course of {duration_phrase} in professional software development, I have developed a reputation for dependable execution, thoughtful collaboration, and a steady focus on outcomes that matter to the business.',
    ],
    'experiences' => [
        'Most recently{recent_company_phrase}, I served as a {title_phrase}, where I owned features from early planning through production support. I worked closely with product, design, and engineering colleagues to turn ambiguous requirements into dependable software, and I took particular pride in raising the bar for code quality along the way.',
        'In my role as a {title_phrase}{recent_company_phrase}, I was responsible for designing and delivering systems used every day by real customers. That work required balancing technical depth with clear communication, and it taught me how to keep projects moving without sacrificing stability or maintainability.',
        'My experience as a {title_phrase}{recent_company_phrase} has given me a practical understanding of what it takes to deliver software well. I have partnered with stakeholders to shape requirements, broken large problems into manageable pieces, and remained accountable for the result long after the first release.',
        'As a {title_phrase}{recent_company_phrase}, I have consistently focused on work that improves both the product and the team around it. I document decisions carefully, review code with care, and try to leave every system a little clearer and easier to maintain than I found it.',
    ],
    'achievements' => [
        '{achievement_sentence} I would bring the same care and rigor to {company}.',
        '{achievement_sentence} That is the kind of contribution I hope to make at {company}.',
        '{achievement_sentence} I am eager to apply that same standard to the challenges your team is solving.',
        '{achievement_sentence} I regard that combination of judgement and follow-through as essential to lasting technical work.',
    ],
    'skills' => [
        'My technical foundation includes {skills_phrase}. I apply these tools pragmatically, choosing the simplest approach that will serve the product well, and I am always expanding my range as new requirements emerge.',
        'The skills I would bring to {company} include {skills_phrase}. Beyond the tools themselves, I value clear interfaces, sensible documentation, and the kind of consistency that makes a codebase easier for everyone to work in.',
        'I work comfortably with {skills_phrase}, and I have used them to deliver production software rather than merely study them. I learn new technologies quickly and take care to match the tool to the problem at hand.',
        'At the core of my toolkit are {skills_phrase}. I care about writing code that others can read and maintain, and I take the same approach to collaboration: be clear, be dependable, and make the next person\'s work easier.',
    ],
    'extras' => [
        'fit' => [
            'I am equally comfortable working independently and as part of a cross-functional team, and I take pride in communicating progress clearly, flagging risks early, and keeping the commitments I have made.',
            'Colleagues would describe me as steady under pressure and generous with context. I make a point of understanding the wider business goal behind each task so that the technical decisions I make are grounded in something real.',
            'I believe good engineering depends on good collaboration. I invest time in understanding how my colleagues work, share information openly, and try to make the team stronger with every project I join.',
        ],
        'bridge' => [
            'Throughout my career I have tried to combine technical depth with a practical sense of what the business needs, and I would welcome the opportunity to do the same for {company}.',
            'What draws me to this role is the chance to apply established engineering discipline to problems that matter, and to do so alongside colleagues who hold themselves to a high standard.',
        ],
        'boost' => [
            'I would welcome the chance to discuss how these strengths could support your priorities, and I am happy to provide references or work samples on request.',
            'I am confident that my combination of hands-on delivery and collaborative habits would allow me to contribute meaningfully from the first week.',
        ],
        'craft' => [
            'I also believe strongly in continuous improvement, both in the systems I build and in the way I work, and I welcome feedback as a means of raising the quality of my contributions.',
            'I keep a deliberate focus on the long-term health of the codebase. That means writing tests that earn their place, documenting decisions that are not obvious, and resisting shortcuts that simply move the cost elsewhere.',
            'Beyond the immediate deliverable, I care about the habits that sustain a team: thoughtful reviews, honest estimates, and documentation that remains useful six months later.',
        ],
    ],
    'closings' => [
        'I would welcome the chance to discuss how my experience as a {title_phrase} could support {company}\'s goals for this {role} role. Thank you for your time and consideration; I look forward to speaking with you.',
        'My resume is enclosed for your review, and I would be glad to provide any further detail. Thank you for considering my application for the {role} position at {company}.',
        'I appreciate the opportunity to apply and would welcome a conversation about how I can contribute to {company}. Thank you for your consideration, and I look forward to your response.',
        'Thank you for reviewing my application. I would be pleased to discuss how my background fits the {role} opening at {company}, and I look forward to the possibility of contributing to your team.',
    ],
    'valediction' => 'Sincerely,',
];
