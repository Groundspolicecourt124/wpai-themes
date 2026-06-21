// Demo-site content packs for the live previews. Each theme gets its own pack so
// its Playground demo reads like a real, specific site. `image` fields reference a
// cover by base name (see docs/demo/*.jpg); gen-gallery resolves them to URLs.
// DEMO_PACKS is filled per-theme; anything missing falls back to DEFAULT_PACK.

export const COVERS = ['ember', 'tide', 'grove', 'dusk', 'slate', 'sand'];

export const DEFAULT_PACK = {
  site: { name: 'Northwind', tagline: 'Ideas worth shipping.' },
  author: { name: 'Mara Ellison', bio: 'Designer and engineer writing about craft, code, and calm.' },
  menuCategories: ['Design', 'Engineering'],
  pages: [
    {
      title: 'About',
      image: 'slate',
      content:
        '<p>Northwind is a small publication about craft, code, and calm — written for people who build things and care how they feel to use.</p>' +
        '<p>It is run by <strong>Mara Ellison</strong>, a designer and engineer who has spent a decade shipping software and learning, slowly, that less is usually more.</p>' +
        '<p>No trackers, no pop-ups, no newsletter to escape. Just writing, and the occasional good idea worth keeping.</p>',
    },
  ],
  posts: [
    {
      title: 'Designing for calm', category: 'Design', image: 'dusk',
      excerpt: 'Good software feels quiet. Here is how to build interfaces that get out of the way.',
      content:
        '<p>Good software feels quiet. It does the thing you asked, then steps out of the way and leaves room for your own thoughts.</p>' +
        '<h2>Less, but better</h2>' +
        '<p>Every element on a page is a small request for attention. The craft is deciding which requests are worth making.</p>' +
        '<blockquote>Simplicity is not the absence of detail. It is detail spent only where it counts.</blockquote>' +
        '<p>When in doubt, remove. The page that remains is almost always stronger.</p>',
    },
    {
      title: 'The real cost of a slow website', category: 'Engineering', image: 'tide',
      excerpt: 'Speed is a feeling before it is a number. Here is where the milliseconds hide.',
      content:
        '<p>Speed is a feature you feel before you can name it. A page that loads instantly feels trustworthy; a slow one feels broken even when everything works.</p>' +
        '<h2>Where the milliseconds go</h2>' +
        '<ul><li>Web fonts that block the first paint</li><li>Hero images larger than their container</li><li>Scripts that run before anyone has scrolled</li></ul>' +
        '<p>Trim each one and the whole experience lightens.</p>',
    },
    {
      title: 'Notes on shipping small', category: 'Craft', image: 'ember',
      excerpt: 'The smallest version of an idea that still helps someone is the best place to start.',
      content:
        '<p>The smallest version of an idea that still helps someone is usually the right place to start. You learn more from one real user than from a month of speculation.</p>' +
        '<h2>Ship, then listen</h2>' +
        '<p>Release early, watch closely, and let the next step reveal itself. Momentum compounds.</p>',
    },
    {
      title: 'A field guide to better mornings', category: 'Life', image: 'grove',
      excerpt: 'Protect the first hour. The rest of the day tends to follow it.',
      content:
        '<p>The first hour sets the temperature for everything after it. Guard it like it matters, because it does.</p>' +
        '<h2>Three small rituals</h2>' +
        '<ul><li>Light before screens</li><li>One page of writing, badly, before the inbox</li><li>A walk short enough that you will actually take it</li></ul>',
    },
    {
      title: 'What old maps teach us about design', category: 'Ideas', image: 'sand',
      excerpt: 'The best maps leave things out on purpose. So should the best products.',
      content:
        '<p>A map that showed everything would be useless — and exactly the size of the world. Every great map is an argument about what matters.</p>' +
        '<h2>The art of leaving out</h2>' +
        '<p>Designers are mapmakers. We choose a scale, pick a projection, and decide which roads earn a line. The omissions are the design.</p>',
    },
  ],
  comments: [
    { author: 'Jonah Reed', content: 'This put words to something I have felt for years but could not name.' },
    { author: 'Priya Nair', content: '"Detail spent only where it counts" — stealing that for our review on Monday.' },
    { author: 'Sam Okafor', content: 'Came for the typography, stayed for the philosophy. More of this, please.' },
  ],
};

// Per-theme packs (slug -> pack). Filled by the demo-tailoring pass; any theme
// not listed here uses DEFAULT_PACK.
export const DEMO_PACKS = {
  "aurora": {
    "site": {
      "name": "The Long Way Round",
      "tagline": "Essays on reading, writing, and paying closer attention."
    },
    "author": {
      "name": "Marin Holloway",
      "bio": "Writer and lapsed librarian who keeps a slow journal about craft, books, and the art of noticing."
    },
    "menuCategories": [
      "Essays",
      "Notes",
      "Reading"
    ],
    "pages": [
      {
        "title": "About",
        "image": "sand",
        "content": "<p>I'm Marin. For nine years I shelved other people's books for a living, and somewhere in the quiet between requests I started keeping a notebook of my own. The Long Way Round is what spilled out of it: essays that take the scenic route, notes I scribble in margins, and the occasional argument with a book I love.</p><p>I write here about attention mostly. How hard it has become to give, how much it changes a day when you do, and how reading and writing are really just two disciplines for the same stubborn practice. I am not interested in productivity. I am interested in what stays with you after the lamp is off.</p><p>There's no schedule and no newsletter funnel. Things go up when they're ready. If something here makes you reach for a pen, or a book, or the window, then we've understood each other.</p>"
      }
    ],
    "posts": [
      {
        "title": "The Hour Before the Day Asks Anything",
        "category": "Essays",
        "image": "dusk",
        "excerpt": "On the small, unclaimed hour at dawn, and why I have stopped trying to make it useful.",
        "content": "<p>There is an hour at the start of the day when nothing has asked anything of me yet. The phone is still face-down. The kettle hasn't clicked. For a long time I tried to fill that hour with something improving, and for a long time it slipped through my fingers anyway.</p><p>What I've learned is that the hour does not want to be spent. It wants to be inhabited. I sit with a cup going cold and watch the light decide what color it's going to be, and somewhere in that watching the day arranges itself more kindly than it would have otherwise.</p><blockquote>The most productive thing I do all day is the thing that produces nothing.</blockquote><p>I don't recommend this as a system. Systems are how the morning gets colonized. I only mean that the unclaimed hour is worth protecting, and that protecting it costs less than you think and returns more than you'd believe.</p>"
      },
      {
        "title": "What I Underline, and Why",
        "category": "Reading",
        "image": "grove",
        "excerpt": "A small confession about the pencil marks I leave in the margins of everything I read.",
        "content": "<p>I am a margin-writer. Pristine books make me nervous; I never quite trust a person who finishes a novel and leaves it looking unread. The pencil is how I argue back, how I agree, how I mark the sentence that knocked the wind out of me so I can find it again at two in the morning.</p><p>Over the years a grammar has formed without my deciding it. Here is roughly what the marks mean:</p><ul><li>A single line: this is true, and I want to remember it's true.</li><li>A double line: this changed something, handle with care.</li><li>A question mark: I don't believe you yet, but I'm listening.</li><li>A star: come back here when you've forgotten why you write at all.</li></ul><p>Years later, the marks are a map of a former self. I open an old paperback and meet the person who underlined it, and we don't always agree. That disagreement is, I think, the whole point of keeping the books.</p>"
      },
      {
        "title": "On Finishing Badly",
        "category": "Essays",
        "image": "dusk",
        "excerpt": "Why I have made peace with abandoning books, projects, and the version of me that thought quitting was failure.",
        "content": "<p>I used to finish everything. Every book I started, every dreadful film, every plan I'd announced too loudly to back out of. I mistook endurance for character. It took me an embarrassingly long time to notice that a thing finished out of stubbornness teaches you nothing except how to be stubborn.</p><p>Now I quit. Gently, and more often than I'll admit. A novel that doesn't earn its three hundredth page goes back on the shelf without ceremony. A sentence that won't come right after an afternoon gets left for the morning, or left for good.</p><p>The fear is that quitting is a slope, that one abandoned book leads to an abandoned life. I haven't found that to be true. What I've found is that letting go of the wrong thing is the only way I've ever made room for the right one.</p>"
      },
      {
        "title": "Notes From a Slow Week",
        "category": "Notes",
        "image": "sand",
        "excerpt": "Scraps from seven unremarkable days that turned out to be worth writing down.",
        "content": "<p>I keep a running page for the weeks that don't amount to an essay. Here is most of one, lightly tidied.</p><p>Monday: the bread didn't rise and I ate it anyway, dense as a doorstop, and it was somehow exactly right with butter. Tuesday: a stranger held a door and we both said sorry, which is the most honest greeting our country has. Wednesday: read forty pages standing up because sitting felt like giving in to the rain.</p><p>Thursday through Saturday blur, the way good weeks do. Sunday I wrote nothing and felt no guilt, which after years of practice is its own small achievement. None of this is news. That's the whole appeal. The week asked very little of me and I have decided, in return, to remember it.</p>"
      }
    ],
    "comments": [
      {
        "author": "Tessa Bramble",
        "content": "The unclaimed hour. I've been calling mine 'the no' and protecting it like a dragon, but yours is the better name. Thank you for putting words to it."
      },
      {
        "author": "Owen Carr",
        "content": "Read this on the train with my coffee going cold in exactly the way you describe. Felt seen, and slightly called out. Both welcome."
      },
      {
        "author": "Marin Holloway",
        "content": "Tessa, 'the no' is wonderful and I may steal it. Owen, the cold coffee is non-negotiable apparently. Glad you both found the hour."
      }
    ]
  },
  "monolith": {
    "site": {
      "name": "Quarry & Co.",
      "tagline": "We build sturdy things on purpose."
    },
    "author": {
      "name": "Dana Reyes",
      "bio": "Principal at Quarry & Co. Designs the system, then proves it in code."
    },
    "menuCategories": [
      "Work",
      "Process",
      "Notes"
    ],
    "pages": [
      {
        "title": "About",
        "image": "slate",
        "content": "<p>Quarry & Co. is a two-discipline studio: one half design, one half engineering, no handoff in between. We take products from a blank canvas to something that ships, holds up under traffic, and reads clearly to the people using it. Small team, direct line, no account managers.</p><p>We work in short, honest cycles. A week of sketching becomes a week of building, and the build talks back. Most of what we ship started as a constraint we refused to design around until we understood it. That stubbornness is the method.</p><p>If you have a hard problem and a real deadline, we are good company. If you want a deck and a maybe, we are probably not.</p>"
      }
    ],
    "posts": [
      {
        "title": "Casework: A Dashboard That Earns Its Density",
        "category": "Work",
        "image": "slate",
        "excerpt": "Forty metrics, one screen, and a redesign that made density a feature instead of an apology.",
        "content": "<p>The brief was blunt: operators live in this dashboard for eight hours a day, and the old one made them squint. Our predecessors had solved the crowding by hiding things behind tabs. The operators solved it by keeping six tabs open at once.</p><h2>Density is not the enemy</h2><p>So we stopped fighting the density and started ranking it. Every number on the screen got a job: glance, scan, or drill. Glance metrics are huge and dumb. Scan metrics live in tight rows you can read with a saccade. Drill metrics hide one click deep, and only there.</p><blockquote>The screen should feel busy the way a cockpit feels busy: everything present, nothing shouting.</blockquote><p>Three weeks in, support tickets about \"where did X go\" dropped to zero, because nothing went anywhere. It just learned its place.</p>"
      },
      {
        "title": "Building a Design System Nobody Has to Babysit",
        "category": "Process",
        "image": "dusk",
        "excerpt": "Tokens, a strict component contract, and the boring discipline that keeps a system alive after launch.",
        "content": "<p>Most design systems die the same way: a hero ships it, the hero leaves, and within a quarter every team has forked a button. We wanted one that survived its own founders.</p><p>The trick was making the right thing the easy thing. Components ship with their constraints baked in, not documented in a wiki nobody reads. You cannot pass a color that is not a token. You cannot set a spacing value off the scale. The system says no before a review has to.</p><ul><li>One source of truth for tokens, consumed by both Figma and code.</li><li>Components that fail loudly in dev when used wrong.</li><li>A changelog written for humans, not for the linter.</li></ul><p>A year later the original team had rotated out and the system was still coherent. That is the whole win. Not elegance, durability.</p>"
      },
      {
        "title": "On Systems: The Cost of a Clever Abstraction",
        "category": "Notes",
        "image": "tide",
        "excerpt": "Every abstraction is a loan against the next engineer's understanding, and the interest compounds.",
        "content": "<p>We deleted four hundred lines of \"flexible\" config last month and replaced it with two hundred lines of plain code. The product did exactly the same thing afterward. It just stopped requiring a tour guide.</p><p>Abstractions are not free. They buy you reuse and they charge you comprehension. The bill comes due the first time someone new has to change behavior the abstraction did not anticipate, and they always have to.</p><blockquote>Write the dumb version first. Earn the clever one with a second use case, not a hunch.</blockquote><p>This is not a case against abstraction. It is a case for paying for it honestly, when you can name the thing it saves you and not a minute before.</p>"
      },
      {
        "title": "Building the Render Pipeline Twice",
        "category": "Work",
        "image": "dusk",
        "excerpt": "We shipped the fast version, watched it break under real data, then rebuilt it to be slow in the right places.",
        "content": "<p>The first pipeline was a sprint. It rendered everything eagerly, looked great in the demo, and fell over the moment a customer fed it ten thousand records instead of ten.</p><p>The rebuild was less glamorous and far more useful. We made it lazy by default and eager only where the eye actually lands first, above the fold and nowhere else. The hero paints instantly; the long tail loads as you reach it.</p><h2>Slow on purpose</h2><p>Performance work is mostly deciding what is allowed to be slow. Once we let the bottom of the page be lazy, the top of the page got to be instant. The customer with ten thousand records never noticed the pipeline at all, which is exactly the review we wanted.</p>"
      }
    ],
    "comments": [
      {
        "author": "Marcus Lindqvist",
        "content": "The glance/scan/drill framing finally gave me language for an argument I have been losing in design reviews for years. Stealing this."
      },
      {
        "author": "Priya N.",
        "content": "We inherited the exact dashboard you are describing, six tabs and all. Sending this to my lead."
      },
      {
        "author": "Theo",
        "content": "\"The screen should feel busy the way a cockpit feels busy.\" That line reframed the whole project for me. Thank you."
      }
    ]
  },
  "verdant": {
    "site": {
      "name": "Fernwell Studio",
      "tagline": "A garden, a studio, and slow seasons of care."
    },
    "author": {
      "name": "Maren Hollis",
      "bio": "Herbalist and garden teacher tending a small backyard studio in the Finger Lakes."
    },
    "menuCategories": [
      "Wellness",
      "Garden",
      "Studio"
    ],
    "pages": [
      {
        "title": "About",
        "image": "sand",
        "content": "<p>Fernwell Studio started as a single raised bed and a kettle that never quite stopped whistling. I am Maren Hollis, and for the better part of fifteen years I have been learning the slow trades: growing herbs, drying them in paper bundles, and teaching small groups how to make their kitchens feel a little more like a remedy and a little less like a rush.</p><p>The studio sits behind the garden, a converted shed with good light and a long worktable that has held more chamomile than I can count. We keep our classes small on purpose. There is no app to download, no streak to maintain, no leaderboard. Just a season, a plant, and the people who showed up to learn it.</p><p>If you are tired in the way that sleep does not fix, you are exactly who this place is for. Come sit by the rosemary. We will figure out the rest together.</p>"
      }
    ],
    "posts": [
      {
        "title": "What to Plant When You Want to Slow Down",
        "category": "Garden",
        "image": "grove",
        "excerpt": "A short list of forgiving, fragrant herbs for the gardener who needs the garden to be gentle this year.",
        "content": "<p>Not every season is a season for ambition. Some years the right goal is simply to keep a few green things alive and let them ask very little of you in return. These are the plants I hand to anyone who tells me they want a garden but cannot promise it much.</p><h2>The forgiving four</h2><ul><li><strong>Mint</strong> — nearly impossible to kill, happy in a pot, and a fistful of it turns a glass of water into something you actually want to drink.</li><li><strong>Calendula</strong> — cheerful, self-seeding, and the petals dry beautifully for winter teas.</li><li><strong>Thyme</strong> — low, woody, and content with poor soil and your forgetfulness.</li><li><strong>Lemon balm</strong> — crush a leaf when you walk past and your whole afternoon resets.</li></ul><p>Plant two of these, not all four. The garden is not a checklist. It is a small standing invitation to step outside and notice that something is still growing, even on the days you are not.</p>"
      },
      {
        "title": "The Five-Minute Tea Ritual That Actually Sticks",
        "category": "Wellness",
        "image": "ember",
        "excerpt": "Why a warm cup at the same hour each day does more for a frayed nervous system than any elaborate routine.",
        "content": "<p>People come to the studio wanting a wellness overhaul. They leave with a teaspoon of dried chamomile and one instruction: make it at the same time every day. The boring part is the part that works.</p><blockquote>A ritual is not what you do once with great feeling. It is what you do a hundred times with very little.</blockquote><p>Pick an hour that already exists in your day, the gap before the kids wake or the quiet after dinner, and pour hot water over something fragrant. Hold the cup before you drink it. That pause, repeated, teaches your body that the day has a place to rest. No app reminder, no streak to break, just a warm cup and a returning.</p><p>Start with chamomile and lemon balm in equal parts. After a week, you will not need me to tell you what it is for.</p>"
      },
      {
        "title": "Inside the Studio: How a Class Comes Together",
        "category": "Studio",
        "image": "sand",
        "excerpt": "A look behind the worktable at how a single afternoon of herb-drying takes shape, from harvest to paper bundle.",
        "content": "<p>People sometimes ask what happens before a class, as if there is a curtain to pull back. There is, a little. The morning of a session, I am in the garden by seven, cutting while the dew has lifted but the heat has not yet arrived. Herbs picked in that narrow window keep their oils, and the studio smells of it for days.</p><p>By mid-morning the long table is set: twine, brown paper, scissors worn smooth, and small jars labeled in my own uneven hand. We work slowly and we talk, and somewhere around the second cup of tea the room loosens. That is the real lesson, not the drying technique but the permission to do one thing carefully and let it take the time it takes.</p><p>Classes run small, six seats at most. If you have been meaning to come, the autumn sessions open next month.</p>"
      },
      {
        "title": "Putting the Garden to Bed for Winter",
        "category": "Garden",
        "image": "grove",
        "excerpt": "The quiet, satisfying work of closing down the beds, and why a tidy dormancy is its own kind of harvest.",
        "content": "<p>There is a particular peace in the last warm week of autumn, when the work shifts from growing to gathering and then, finally, to rest. Putting the garden to bed is not an ending. It is the part of the cycle where you trade abundance for order, and order, it turns out, is its own comfort.</p><p>Cut the spent stems but leave a few seed heads standing for the birds. Mulch the beds thick. Lift the tender roots you want to keep and tuck the rest under leaves. Then do the thing most of us forget: stop. Stand at the edge of the quiet beds and let yourself feel finished.</p><p>The garden will keep without you all winter. That is the gift dormancy offers, to the soil and, if you let it, to you.</p>"
      },
      {
        "title": "A Honey and Thyme Remedy for the First Cold of the Season",
        "category": "Wellness",
        "image": "ember",
        "excerpt": "A simple kitchen oxymel you can stir together in ten minutes and reach for when the cough arrives.",
        "content": "<p>The first cold of autumn arrives like clockwork, usually the week the windows finally close. This is the remedy I keep on the shelf for it, an old-fashioned oxymel that is mostly honey, a little vinegar, and a generous handful of thyme.</p><h2>How to make it</h2><ul><li>Fill a clean jar a third full with fresh thyme sprigs.</li><li>Add raw honey and apple cider vinegar in roughly equal parts until the jar is full.</li><li>Cap it, shake it, and let it sit somewhere dark for two weeks, shaking when you remember.</li><li>Strain, and keep it in the fridge through the cold months.</li></ul><p>A spoonful in warm water soothes a scratchy throat and tastes far better than anything from the pharmacy. None of this replaces a doctor when you need one, but for the ordinary seasonal scratch, it has earned its place on my shelf for years.</p>"
      }
    ],
    "comments": [
      {
        "author": "Priya",
        "content": "I planted lemon balm after reading this and you were right, I crush a leaf every time I pass it now. Small thing, big difference."
      },
      {
        "author": "Tom R.",
        "content": "The forgiving four is exactly the permission I needed. I always overcommit and then feel guilty when half the bed dies. Two plants it is."
      },
      {
        "author": "Maren Hollis",
        "content": "So glad it landed, Tom. Two well-tended pots beat a neglected garden every time. Come by the studio in spring and I will get you started with starts."
      }
    ]
  },
  "ledger": {
    "site": {
      "name": "The Meridian Review",
      "tagline": "Reporting that holds its ground."
    },
    "author": {
      "name": "Eleanor Brandt",
      "bio": "Lead writer and founding editor of The Meridian Review, covering the seams where culture, money, and power overlap."
    },
    "menuCategories": [
      "Culture",
      "Business",
      "Opinion",
      "Politics"
    ],
    "pages": [
      {
        "title": "About",
        "image": "slate",
        "content": "<p>The Meridian Review began with a stubborn premise: that the most important stories live in the overlap between beats, not inside any single one of them. A culture story is usually a business story wearing better clothes. A political fight is almost always an argument about who pays. We exist to report from that overlap, plainly and without flinching.</p><p>We are a small newsroom by design. We would rather publish four pieces a week that we can defend line by line than forty we cannot. Every article here is reported, edited, and stood behind by a named human being. We do not run anonymous takes, we do not launder press releases into news, and we correct ourselves in public when we get something wrong.</p><p>If you find a claim here that does not hold up, tell us. The masthead is small enough that the email reaches a person, not a void. That is the whole point.</p>"
      }
    ],
    "posts": [
      {
        "title": "The Streaming Wars Are Over. The Audience Lost.",
        "category": "Culture",
        "image": "ember",
        "excerpt": "After a decade of frictionless abundance, the platforms have quietly rebuilt every wall they once promised to tear down.",
        "content": "<p>For roughly ten years, the pitch was simple and seductive: pay one modest fee, and the entire history of filmed entertainment would be yours, anywhere, instantly. That world existed briefly, and it is gone. What replaced it is a thicket of overlapping subscriptions, rotating catalogs, and ad tiers that cost more to remove than the original service once cost to own outright.</p><h2>The math stopped working</h2><p>The turning point was not a single decision but a collective one. Once growth slowed, every platform reached for the same levers at the same time: raise prices, add advertising, crack down on shared logins, and shorten the window before a title vanishes. Each move was rational in isolation. Together they reassembled exactly the friction streaming was supposed to abolish.</p><blockquote>We were sold a library. We rented a turnstile.</blockquote><p>The viewer who once paid nine dollars for near-total access now juggles five services, none complete, and still cannot reliably find the film they wanted on a Tuesday night. The wars are over because the combatants made peace with each other. The terms of that peace were written on the audience's bill.</p>"
      },
      {
        "title": "Inside the Quiet Collapse of the Office Lease",
        "category": "Business",
        "image": "tide",
        "excerpt": "The commercial real estate reckoning everyone predicted has finally arrived, just slowly enough that no one called it a crisis.",
        "content": "<p>There was supposed to be a dramatic moment, a single quarter when the bottom fell out of the office market. It never came. Instead the decline arrived as a slow leak: a lease not renewed here, a floor sublet there, a tower refinanced at terms its owners will spend a decade regretting. The crisis was real. It simply refused to be photogenic.</p><p>What makes the unwinding so hard to read is that the pain is distributed unevenly across the same skyline. A handful of newer, amenity-heavy buildings are nearly full. The older stock behind them sits at occupancy levels that would have triggered headlines in any previous cycle.</p><ul><li>Top-tier towers are competing for a shrinking pool of premium tenants.</li><li>Mid-market buildings face conversions that pencil out for almost none of them.</li><li>Municipal budgets built on commercial property taxes are quietly bracing.</li></ul><p>The result is a market that is not crashing so much as quietly repricing itself, lease by lease, in a language only the people signing the documents can fully read.</p>"
      },
      {
        "title": "Stop Calling It a Talent Shortage",
        "category": "Opinion",
        "image": "dusk",
        "excerpt": "When employers cannot fill a role for two years, the problem is rarely the labor market and almost always the offer.",
        "content": "<p>There is a phrase that has hardened into conventional wisdom across boardrooms and op-ed pages alike: the talent shortage. It is invoked to explain unfilled roles, stalled projects, and missed targets. It is also, in most cases, a polite fiction that lets an employer avoid looking in the mirror.</p><p>A shortage implies that the people simply do not exist. But the workers usually do exist. They have looked at the posting, weighed the pay against the demands, noticed the absence of flexibility or advancement, and quietly declined. That is not scarcity. That is a market clearing exactly as it should, against terms the buyer does not want to accept.</p><blockquote>If no one will take the deal, the deal is the problem.</blockquote><p>Reframing the conversation is not a semantic exercise. As long as the story is shortage, the proposed fixes are pipelines, visas, and training programs aimed at producing more applicants. The moment the story becomes the offer, the fix lands where it belongs: on pay, conditions, and the basic respect a role extends to whoever fills it.</p>"
      },
      {
        "title": "The Local Election No One Covered Is Reshaping the State",
        "category": "Politics",
        "image": "slate",
        "excerpt": "While national attention chased the marquee races, a handful of county boards quietly rewired how millions of people will be governed.",
        "content": "<p>The cameras were pointed somewhere else. On the night the results came in, every major outlet led with the statewide contests, the ones with the recognizable names and the eight-figure ad budgets. Meanwhile, in races that drew a fraction of the coverage, county boards changed hands in ways that will outlast any of the headliners.</p><p>This is the persistent blind spot of modern political reporting. Attention flows to the offices with the biggest titles, but a startling amount of day-to-day governance happens far below them, in bodies that set property assessments, approve infrastructure, and decide how elections themselves are administered.</p><h2>Where power actually sits</h2><p>The new majorities now control decisions that touch residents far more directly than most of what happens in the capital. Zoning that determines whether housing gets built. Budgets that fund or starve local services. Rules that shape how the next election is run.</p><p>None of it was secret. All of it was on the ballot. It simply happened in the part of the room the cameras had decided was not worth filming.</p>"
      }
    ],
    "comments": [
      {
        "author": "Marcus Feld",
        "content": "This finally put words to something I have felt every time I open a streaming app and pay more to watch less. Sharp piece."
      },
      {
        "author": "Priya Nair",
        "content": "The turnstile line is going to stick with me. Would love a follow-up on whether physical media is genuinely coming back or just nostalgia."
      },
      {
        "author": "Dana Whitmore",
        "content": "Refreshing to read something that does not just blame the consumer for cutting the cord. The platforms made these choices on purpose."
      }
    ]
  },
  "nimbus": {
    "site": {
      "name": "Nimbus",
      "tagline": "The product platform for teams that ship without slowing down."
    },
    "author": {
      "name": "Priya Raman",
      "bio": "Head of Product at Nimbus, writing about what we build, why we build it, and what we learn along the way."
    },
    "menuCategories": [
      "Product",
      "Engineering",
      "Company"
    ],
    "pages": [
      {
        "title": "About",
        "image": "slate",
        "content": "<p>Nimbus started with a frustration most product teams know too well: the gap between having an idea and getting it in front of real users keeps getting wider, not narrower. Every new tool promised speed and quietly added a step. We wanted the opposite.</p><p>So we built a platform that collapses the distance between a rough concept and a shipped feature. Flags, rollouts, metrics, and feedback live in one place, so a small team can move like a focused one and a large team can stay aligned without endless meetings. No glue code, no spreadsheet of who-owns-what, no waiting on a release train.</p><p>We are a remote-first company spread across nine time zones, building in the open and obsessed with the unglamorous details that make software feel fast. If that sounds like your kind of place, the latest from the team is right here on the blog.</p>"
      }
    ],
    "posts": [
      {
        "title": "Introducing Instant Rollouts",
        "category": "Product",
        "image": "dusk",
        "excerpt": "Ship a feature to one percent of users or one hundred, watch it land in real time, and roll it back in a single click.",
        "content": "<p>Today we are launching Instant Rollouts, the fastest way to get a new feature in front of the exact users you want and nobody you don't. You pick the audience, set the percentage, and watch adoption update live on the same screen.</p><h2>Why we built it</h2><p>Releasing software should feel like turning a dial, not pulling a lever you can't reverse. With Instant Rollouts every change is reversible in one click, so the scariest part of shipping, the moment right after you press deploy, becomes the calmest.</p><blockquote>The team that ships small and often always beats the team that ships big and rarely.</blockquote><p>Instant Rollouts is available to every workspace starting today. Open the Releases tab and your first gradual rollout takes about thirty seconds to set up.</p>"
      },
      {
        "title": "How We Cut Cold Start Times by 80 Percent",
        "category": "Engineering",
        "image": "tide",
        "excerpt": "A deep dive into the caching layer, connection pooling, and one embarrassing config flag that was hiding in plain sight.",
        "content": "<p>For months our biggest support theme was the same: the first request after a quiet period felt sluggish. Warm requests were fast, but that initial cold start undermined the whole experience. We decided to fix it for good.</p><p>The investigation surfaced three culprits, and they were not equally guilty:</p><ul><li>Connection pools that drained completely during idle windows</li><li>A cache that warmed lazily instead of on boot</li><li>A single retry flag left at a generous default since our very first deploy</li></ul><h2>The fix that mattered most</h2><p>Pre-warming the cache on boot and keeping a small floor of pooled connections did the heavy lifting. The retry flag was the embarrassing one: flipping a single value shaved hundreds of milliseconds off the tail. Cold starts are now eighty percent faster, and the support theme has quietly disappeared.</p>"
      },
      {
        "title": "Nimbus Raises Series A to Build the Product Layer",
        "category": "Company",
        "image": "ember",
        "excerpt": "We closed a round to invest in reliability, expand the team, and keep our core plan free for small teams.",
        "content": "<p>We are thrilled to share that Nimbus has raised a Series A. More than the number, what excites us is what it lets us do: invest deeply in reliability, grow the team thoughtfully, and keep the platform genuinely useful for the smallest teams.</p><p>Three commitments come with this round. We are doubling the engineering team focused on uptime and performance. We are keeping our core plan free for teams of up to five, forever. And we are publishing a public status and incident history so you always know exactly where we stand.</p><p>Thank you to every team that trusted us early, filed a bug, or told us what was missing. You shaped this product more than any pitch deck ever could. The best part is still ahead.</p>"
      },
      {
        "title": "A Simpler Way to Read Your Metrics",
        "category": "Product",
        "image": "dusk",
        "excerpt": "We redesigned dashboards around the one question every team actually asks: is this change working?",
        "content": "<p>Dashboards have a way of growing until nobody trusts them. Ours did too. So we stepped back and asked what people really come to a metrics screen to learn, and the answer was almost always the same single question: is this change working or not?</p><h2>One question, front and center</h2><p>The new dashboard answers that first. Every release now shows a clear before-and-after on the metrics you chose to watch, with a confidence signal so you know whether the difference is real or just noise. The deep, drill-down views are still there, just one tap away instead of crowding the front page.</p><p>It is live for everyone now. We think you will reach for it a lot, and we would love to hear which view you pin first.</p>"
      }
    ],
    "comments": [
      {
        "author": "Marcus Lee",
        "content": "We switched our rollout process to this last week and already caught a bad change before it hit more than a handful of users. The one-click rollback is the part my whole team keeps talking about."
      },
      {
        "author": "Dana Whitfield",
        "content": "Thirty seconds to set up a gradual rollout is not an exaggeration, I timed it. Great launch."
      },
      {
        "author": "Sofia Almeida",
        "content": "Please keep writing these. The honesty about the embarrassing config flag is exactly why I trust your team with our releases."
      }
    ]
  },
  "sonnet": {
    "site": {
      "name": "Mara Ellison",
      "tagline": "Letters and essays from a slow, lamplit life."
    },
    "author": {
      "name": "Mara Ellison",
      "bio": "Essayist and letter-writer. I keep a quiet journal about attention, weather, and the long art of staying."
    },
    "menuCategories": [
      "Essays",
      "Letters",
      "Notes"
    ],
    "pages": [
      {
        "title": "About",
        "image": "slate",
        "content": "<p>I am Mara Ellison, and this is where I write the long way around. For most of my life I mistook speed for seriousness; I wrote fast, lived fast, and then wondered why so little of it stayed. These pages are my correction. I write at the pace of a kettle coming to the boil, and I publish only when a sentence has stopped fidgeting.</p><p>What you will find here is plain: <em>essays</em> when I have a thought worth turning over in the light, <em>letters</em> when I want to speak to one person rather than a crowd, and <em>notes</em> when something small refuses to be forgotten. I am drawn to dusk, to second drafts, to the particular dignity of ordinary afternoons. I am suspicious of certainty and fond of the word <em>perhaps</em>.</p><p>If a piece here makes your own life feel a half-degree warmer or quieter, then it has done the only work I ask of it. Thank you for reading slowly. It is the kindest thing a stranger can do.</p>"
      }
    ],
    "posts": [
      {
        "title": "The Hour Before the Lamps",
        "category": "Essays",
        "image": "dusk",
        "excerpt": "There is a thin seam of the day, just before the lamps go on, when the world forgets to perform and is simply itself.",
        "content": "<p>There is a particular hour I have spent most of my life trying to describe and most of my life failing to. It arrives in the last violet minutes before the lamps come on, when the rooms of a house are neither lit nor dark but held in a kind of grey suspension, and everything in them — the unwashed cup, the coat over the chair, your own two hands — seems to be waiting politely to be noticed.</p><p>I used to switch the lights on at once, the way you silence a phone that has begun to ring at an inconvenient time. It took me years to understand that the dimness was not an absence of something but a presence; that the hour before the lamps is the only part of the day with nothing to sell you.</p><blockquote>To love the dusk is to make peace with the fact that some things are clearest precisely when you can no longer see them sharply.</blockquote><p>Now I sit in it on purpose. I let the room go soft. I do not reach for the switch until the dark has finished telling me whatever it came to say, and I have found, more often than not, that it came to say: <em>this is enough, this is already enough.</em></p>"
      },
      {
        "title": "A Letter to Whoever Is Awake at Three",
        "category": "Letters",
        "image": "slate",
        "excerpt": "I do not know your name, but I know the ceiling you are staring at, and I wanted to write to you before morning argues you out of it.",
        "content": "<p>Dear you,</p><p>I am writing this at the hour you are most likely reading it — that long, charcoal stretch after the world has gone to bed and before it has the decency to wake. I know the ceiling you are studying. I have memorised my own. There is a particular loneliness to being the only lit window on a dark street, and I want you to know that mine is lit too.</p><p>Here is the small, unglamorous thing I have learned about three in the morning: it lies. It tells you that the fear is permanent and the morning is theoretical. Both are untrue. The morning is on its way even now, slow and certain as a tide, and it will bring with it the ordinary mercy of coffee and grey light and a list of things that need doing.</p><p>So hold on, if you can. Not heroically — just the way you hold a railing on the stairs. I am holding mine. We are, the two of us and everyone else with a lamp on tonight, getting through the same long dark by the same dull and reliable trick: one breath, and then the next one.</p><p>Yours, from another lit window,<br>Mara</p>"
      },
      {
        "title": "On Keeping a Commonplace Book",
        "category": "Essays",
        "image": "sand",
        "excerpt": "A commonplace book is not a diary; it is a net you throw across the years to catch the lines that would otherwise drown.",
        "content": "<p>For three hundred years, readers kept what they called a commonplace book: a plain notebook into which they copied, by hand, the sentences that had struck them. Not their own thoughts — other people's. A passage from a sermon, a line of verse, a remark overheard on a coach. It was reading as a kind of harvesting.</p><p>I have kept one for nineteen years now, and it has become the truest autobiography I own. It records not what I did but what I noticed, which is the more honest measure of a life. Turning its pages, I can watch myself change by what I chose to copy out:</p><ul><li>At twenty, I copied things that sounded clever.</li><li>At thirty, things that sounded brave.</li><li>Now I copy things that sound true, which is rarer, and quieter, and almost never clever at all.</li></ul><p>If you have never kept one, begin tonight with a single sentence — any sentence that made you look up from the page. You will not understand for years what you are building. You are building a portrait of your own attention, and one day it will tell you, with great tenderness, exactly who you have been.</p>"
      },
      {
        "title": "The Weather Is a Kind of Company",
        "category": "Notes",
        "image": "dusk",
        "excerpt": "When the rain came in off the hills this afternoon, it felt less like a forecast and more like a visitor I had been expecting.",
        "content": "<p>The rain arrived this afternoon the way an old friend does — without knocking, and exactly when needed. I had been at the desk too long, the sentences had gone stiff, and then the first drops struck the window and the whole grey weight of the sky leaned gently against the glass.</p><p>I think we underrate the weather as company. It asks nothing of us and yet it changes the room. A storm makes the kitchen feel like a harbour. A long still fog turns the morning confessional. Today's rain simply sat with me, drumming its patient fingers on the sill, and I found I could write again — not because it inspired me, but because it was there, and being witnessed by something larger than yourself is a quiet permission to continue.</p><p>It has eased now to a soft, steady hush. I am leaving the window open a crack so I can keep hearing it. Some company you do not want to show out too soon.</p>"
      },
      {
        "title": "What the Slow River Taught Me About Ambition",
        "category": "Essays",
        "image": "sand",
        "excerpt": "The river does not hurry, and yet there is nothing it does not eventually reach.",
        "content": "<p>There is a river near the house, broad and brown and so slow that on a still day you must watch a leaf for a full minute to be sure it is moving at all. For most of my striving years I found it faintly embarrassing, the way one is embarrassed by a relative who has given up. Get on with it, I wanted to tell the water. You have a sea to reach.</p><p>I am older now, and I have come to think the river is the wisest thing in the valley. It is not lazy; it is certain. It has made an unspoken bargain with time that the fast streams up in the hills have not: it has agreed to be patient in exchange for being unstoppable.</p><blockquote>Ambition, I think, is mostly a quarrel with time — a refusal to believe you will get there if you do not run. The river has settled the quarrel. It simply keeps going, and arrives.</blockquote><p>I walk down to it most evenings now, in the violet hour, and I try to learn the lesson again, because I keep forgetting it. Move at the pace of your own depth. Carry what you carry without splashing. Trust that slow and unceasing will, in the end, wear down stone — and reach, with no hurry at all, the wide and waiting sea.</p>"
      }
    ],
    "comments": [
      {
        "author": "Imogen Hart",
        "content": "I read this in exactly the hour you describe, with the lamps still off, and could not bring myself to turn them on until I had finished. Thank you for naming something I have felt for years without words."
      },
      {
        "author": "Daniel Reyes",
        "content": "\"Nothing to sell you\" — that line stopped me cold. I am going to try sitting in the dusk on purpose this week instead of reaching for the switch."
      },
      {
        "author": "Wren Okafor",
        "content": "There is a generosity in the way you write that I find rare. This felt less like reading an essay and more like being kept company. I will be back at dusk tomorrow."
      }
    ]
  }
};


