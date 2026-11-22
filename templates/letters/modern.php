<?php

declare(strict_types=1);

/**
 * Modern tone: direct, conversational, contemporary.
 *
 * Available placeholders: {company}, {role}, {hiring_manager}, {name},
 * {years_phrase}, {duration_phrase}, {skills_phrase}, {title_phrase},
 * {recent_company_phrase}, {achievement_sentence}.
 */
return [
    'openings' => [
        'I\'m excited to apply for the {role} role at {company}. I\'ve spent {duration_phrase} building products people actually use, and I\'d love to bring that experience to a team that cares about doing the work well.',
        'The {role} opening at {company} caught my attention immediately. As a {title_phrase} with {years_phrase}, I\'m looking for a place where solid engineering and thoughtful product decisions go hand in hand, and that is exactly the impression {company} gives.',
        'I\'d like to put my name forward for the {role} position at {company}. I\'m a {title_phrase} who enjoys turning messy requirements into simple, dependable software, and I think there is a strong fit here.',
        'Consider this my application for the {role} role at {company}. I\'ve built my career around shipping useful software and working well with the people around me, and I would welcome the chance to do that with your team.',
    ],
    'experiences' => [
        'Most recently{recent_company_phrase}, I worked as a {title_phrase}, partnering with product and design to ship features end to end. I\'m comfortable owning the whole lifecycle, from planning and implementation through testing and the inevitable follow-up, and I keep communication direct so nobody has to guess where things stand.',
        'As a {title_phrase}{recent_company_phrase}, I spent most of my time close to the code and close to the customer. I like understanding why a feature exists before deciding how to build it, and that habit has helped me avoid a lot of unnecessary complexity.',
        'I\'ve worked as a {title_phrase}{recent_company_phrase}, usually on small teams where everyone wears a few hats. That environment taught me to be pragmatic, to ask good questions early, and to treat maintainability as part of the feature rather than an afterthought.',
        'My recent work as a {title_phrase}{recent_company_phrase} has centered on making systems simpler and teams faster. I enjoy the unglamorous parts of engineering, such as clear naming, sensible tests, and useful documentation, because they are what make the interesting parts possible.',
    ],
    'achievements' => [
        '{achievement_sentence} I\'d love to bring that same energy to {company}.',
        '{achievement_sentence} That\'s the kind of impact I\'m hoping to have here.',
        '{achievement_sentence} I\'d be glad to go into more detail about how that came together.',
        '{achievement_sentence} It\'s the sort of problem I\'d be excited to take on again.',
    ],
    'skills' => [
        'On the technical side, I work with {skills_phrase}. I pick tools based on the problem in front of me, not the other way around, and I\'m comfortable picking up something new when the situation calls for it.',
        'You\'ll find {skills_phrase} in my toolkit. More importantly, I know how to use them in production: how to test the tricky parts, how to debug without panic, and how to leave a codebase in better shape than I found it.',
        'I spend most of my days with {skills_phrase}, and I enjoy the balance between building quickly and building something that lasts. I write for the next reader, keep dependencies honest, and document the decisions that aren\'t obvious.',
        'My toolkit includes {skills_phrase}. I care about the craft side of engineering as much as the output: readable code, meaningful tests, and interfaces that make sense to the people using them.',
    ],
    'extras' => [
        'fit' => [
            'I\'m at my best on teams that value clear communication, quick feedback, and a healthy balance between shipping and maintaining. I\'m happy to lead when that\'s useful and just as happy to dig into the details myself.',
            'I like working close to the product, not just the ticket queue. Understanding the user behind a feature usually leads to better technical decisions, and it makes the work more satisfying too.',
            'Good collaboration is a technical advantage, not a soft skill. I share context early, ask for feedback while it can still change the outcome, and try to make the people around me more effective.',
        ],
        'bridge' => [
            'I\'m looking for a role where I can keep growing, take ownership of meaningful problems, and work with people who care about the result as much as the process.',
            'What I want next is straightforward: interesting problems, a team that communicates well, and the room to make a real contribution. {company} looks like a place where that is possible.',
        ],
        'boost' => [
            'If it would help, I\'m happy to share examples of the projects behind these results and talk through how I\'d approach the work here.',
            'I\'d rather have a real conversation than guess from a job description, so I\'d welcome the chance to compare notes about what the team needs.',
        ],
        'craft' => [
            'I keep learning deliberately, whether that\'s a new language, a better testing approach, or a tool that removes busywork. I\'d rather understand a system deeply than memorize its surface.',
            'I treat maintainability as part of the feature, not a cleanup task for later. Small things like clear names, honest tests, and sensible boundaries save the team far more time than they cost.',
            'I\'m pragmatic about process and stubborn about quality. I\'d rather ship something simple that works than something clever that nobody wants to maintain.',
        ],
    ],
    'closings' => [
        'I\'d welcome a conversation about how I can help {company} hit its goals in this {role} role. Thanks for your time, and I look forward to hearing from you.',
        'Thanks for considering my application. I\'d be happy to walk through my work and answer any questions about how I\'d approach the {role} role at {company}.',
        'I appreciate you taking a look at my application. If it seems like a fit, I\'d love to talk about what {company} is building and where I could contribute.',
        'Thanks for reading. I\'m genuinely excited about the {role} opening at {company}, and I\'d welcome the chance to talk about how my background lines up with what your team needs.',
    ],
    'valediction' => 'Best regards,',
];
