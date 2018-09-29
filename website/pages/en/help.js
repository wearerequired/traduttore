/**
 * Copyright (c) 2017-present, Facebook, Inc.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */

const React = require('react');

const CompLibrary = require('../../core/CompLibrary.js');

const Container = CompLibrary.Container;
const GridBlock = CompLibrary.GridBlock;

const siteConfig = require(`${process.cwd()}/siteConfig.js`);

function docUrl(doc, language) {
  return `${siteConfig.baseUrl}docs/${language ? `${language}/` : ''}${doc}`;
}

class Help extends React.Component {
  render() {
    const language = this.props.language || '';
    const supportLinks = [
      {
        content: `Learn more using the [official documentation](${docUrl(
          'installation',
          language
        )}).`,
        title: 'Browse Docs',
      },
      {
        content: `You can follow and contact us on [Twitter](https://twitter.com/wearerequired).`,
        title: 'Twitter',
      },
      {
				content: `You can submit [issues](https://github.com/wearerequired/traduttore/issues) or
				[pull requests](https://github.com/wearerequired/traduttore/pulls) for bugs or feature suggestions
				at our [GitHub repisitory](https://github.com/wearerequired/traduttore).`,
        title: 'GitHub',
      },
    ];

    return (
      <div className="docMainWrapper wrapper">
        <Container className="mainContainer documentContainer postContainer">
          <div className="post">
            <header className="postHeader">
              <h1>Need help?</h1>
            </header>
            <p>This project is maintained by a dedicated group of people.</p>
            <p>If you need help with Traduttore, you can try one of the mechanisms below.</p>
            <GridBlock contents={supportLinks} layout="threeColumn" />
          </div>
        </Container>
      </div>
    );
  }
}

module.exports = Help;
