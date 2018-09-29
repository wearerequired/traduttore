/**
 * Copyright (c) 2017-present, Facebook, Inc.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */

// See https://docusaurus.io/docs/site-config for all the possible
// site configuration options.

// List of projects/orgs using your project for the users page.
const users = [
  {
    caption: 'required',
    image: '/traduttore/img/logo-required.png',
    infoLink: 'https://required.com',
    pinned: true,
  },
];

const siteConfig = {
  title: 'Traduttore', // Title for your website.
  tagline: 'WordPress Localization For Everyone',
  url: 'https://wearerequired.github.io', // Your website URL
  baseUrl: '/traduttore/', // Base URL for your project */

  // Used for publishing and more
  projectName: 'traduttore',
  organizationName: 'wearerequired',

  // For no header links in the top nav bar -> headerLinks: [],
  headerLinks: [
    {doc: 'installation', label: 'Docs'},
    {page: 'help', label: 'Help'},
    {page: 'users', label: 'Users'},
  ],

  // If you have users set above, you add it here:
  users,

  /* path to images for header/footer */
  headerIcon: 'img/docusaurus.svg',
  footerIcon: 'img/docusaurus.svg',
  favicon: 'img/favicon.png',

  /* Colors for website */
  colors: {
    primaryColor: '#161616',
    secondaryColor: '#CB1B14',
  },

  /* Custom fonts for website */
  /*
  fonts: {
    myFont: [
      "Times New Roman",
      "Serif"
    ],
    myOtherFont: [
      "-apple-system",
      "system-ui"
    ]
  },
  */

  // This copyright info is used in /core/Footer.js and blog RSS/Atom feeds.
  copyright: `Copyright © ${new Date().getFullYear()} required`,

  highlight: {
    // Highlight.js theme to use for syntax highlighting in code blocks.
    //theme: 'railscasts',
    theme: 'atom-one-dark',
  },

  // Add custom scripts here that would be placed in <script> tags.
  scripts: ['https://buttons.github.io/buttons.js'],

  // On page navigation for the current documentation page.
  onPageNav: 'separate',
  // No .html extensions for paths.
  cleanUrl: true,

  // Open Graph and Twitter card images.
  ogImage: 'img/docusaurus.png',
  twitterImage: 'img/docusaurus.png',

  repoUrl: 'https://github.com/wearerequired/traduttore',
};

module.exports = siteConfig;
