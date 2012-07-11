<?php

/**
 * Copyright (c) 2011 Hearsay News Products, Inc.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy 
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights 
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell 
 * copies of the Software, and to permit persons to whom the Software is 
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in 
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR 
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, 
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE 
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER 
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, 
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE 
 * SOFTWARE.
 */

namespace Hearsay\RequireJSBundle\Templating\Helper;

use Hearsay\RequireJSBundle\Configuration\ConfigurationBuilder;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Templating\Helper\Helper;

/**
 * Templating helper for RequireJS inclusion.
 * @author Kevin Montag <kevin@hearsay.it>
 */
class RequireJSHelper extends Helper
{

    /**
     * @var EngineInterface
     */
    protected $engine = null;
    /**
     * @var ConfigurationBuilder
     */
    protected $configurationBuilder = null;
    /**
     * @var string
     */
    protected $initializeTemplate = null;
    /**
     * @var string
     */
    protected $src = null;

    /**
     * Standard constructor.
     * @param EngineInterface $engine Templating engine.
     * @param ConfigurationBuilder $configurationBuilder Helper to get the live
     * configuration.
     * @param string $initializeTemplate The template name to use for rendering
     * initialization.
     * @param string $src The source URL to use for RequireJS, either as a plain
     * string or as the name of a template to be rendered (for e.g. using Assetic
     * to serve RequireJS).
     */
    public function __construct(EngineInterface $engine, ConfigurationBuilder $configurationBuilder, $initializeTemplate, $src)
    {
        $this->engine = $engine;
        $this->configurationBuilder = $configurationBuilder;
        $this->initializeTemplate = $initializeTemplate;
        $this->src = $src;
    }

    /**
     * Render the RequireJS initialization output.  Available options are:
     *   main:
     *     A module to load immediately when RequireJS is available, via the
     *     data-main attribute.  Defaults to nothing.
     *   configure:
     *     Whether to specify the default configuration options before RequireJS
     *     is loaded.  Defaults to true, and should generally be left this way
     *     unless you need to perform Javascript logic to define the
     *     configuration (e.g. specifying a <code>ready</code> function), in
     *     which case the configuration should be specified manually either
     *     before or after RequireJS is loaded.
     * @link http://requirejs.org/docs/api.html#config
     * @param array $options Rendering options.
     * @return string
     */
    public function initialize(array $options = array())
    {
        $defaults = array(
            'main' => null,
            'configure' => true,
        );
        $options = array_merge($defaults, $options);
        return $this->engine->render($this->initializeTemplate, array(
            'config' => $options['configure'] ? $this->configurationBuilder->getConfiguration() : null,
            'main' => $options['main'],
        ));
    }

    /**
     * Get the source URL for RequireJS, either by rendering a template (if $this->src is
     * readable as a template name) or directly returning the string provided at 
     * construction.
     */    
    public function src()
    {
        if ($this->engine->exists($this->src) && $this->engine->supports($this->src)) {
            return $this->engine->render($this->src);
        } else {
            return $this->src;
        }
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getName()
    {
        return 'require_js';
    }

}
