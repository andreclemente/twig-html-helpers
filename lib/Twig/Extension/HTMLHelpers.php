<?php

/*
 * HTML helpers for Twig.
 *
 * (c) 2013 Nicholas Humfrey
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Twig\TwigFunction;
use Twig\Environment;
use Twig\Extension\AbstractExtension;

class Twig_Extension_HTMLHelpers extends AbstractExtension
{
    public function getName()
    {
        return 'html_helpers';
    }

    public function getFunctions()
    {
        $options = array(
            'needs_context' => true,
            'needs_environment' => true,
            'is_safe' => array('html')
        );

        return array(
            new TwigFunction('check_box_tag', array($this, 'checkBoxTag'), $options),
            new TwigFunction('content_tag', array($this, 'contentTag'), $options),
            new TwigFunction('hidden_field_tag', array($this, 'hiddenFieldTag'), $options),
            new TwigFunction('html_tag', array($this, 'htmlTag'), $options),
            new TwigFunction('image_tag', array($this, 'imageTag'), $options),
            new TwigFunction('input_tag', array($this, 'inputTag'), $options),
            new TwigFunction('label_tag', array($this, 'labelTag'), $options),
            new TwigFunction('labelled_text_field_tag', array($this, 'labeledTextFieldTag'), $options),
            new TwigFunction('link_tag', array($this, 'linkTag'), $options),
            new TwigFunction('password_field_tag', array($this, 'passwordFieldTag'), $options),
            new TwigFunction('radio_button_tag', array($this, 'radioButtonTag'), $options),
            new TwigFunction('reset_tag', array($this, 'resetTag'), $options),
            new TwigFunction('select_tag', array($this, 'selectTag'), $options),
            new TwigFunction('submit_tag', array($this, 'submitTag'), $options),
            new TwigFunction('text_area_tag', array($this, 'textAreaTag'), $options),
            new TwigFunction('text_field_tag', array($this, 'textFieldTag'), $options),
        );
    }

    protected function tagOptions(Environment $env, $options)
    {
        $html = "";
        foreach ($options as $key => $value) {
            if ($key and (!is_null($value) and $value !== '')) {
                $html .= " ".
                    twig_escape_filter($env, $key)."=\"".
                    twig_escape_filter($env, $value)."\"";
            }
        }
        return $html;
    }

    public function htmlTag(Environment $env, $context, $name, $options=array())
    {
        return "<$name".$this->tagOptions($env, $options)." />";
    }

    public function contentTag(Environment $env, $context, $name, $content='', $options=array())
    {
        return "<$name".$this->tagOptions($env, $options).">".
               twig_escape_filter($env, $content).
               "</$name>";
    }

    public function linkTag(Environment $env, $context, $title, $url=null, $options=array())
    {
        if (is_null($url)) {
            $url = $title;
        }
        $options = array_merge(array('href' => $url), $options);
        return $this->contentTag($env, $context, 'a', $title, $options);
    }

    public function imageTag(Environment $env, $context, $src, $options=array())
    {
        $options = array_merge(array('src' => $src), $options);
        return $this->htmlTag($env, $context, 'img', $options);
    }

    public function inputTag(Environment $env, $context, $type, $name, $value=null, $options=array())
    {
        $options = array_merge(
            array(
                'type' => $type,
                'name' => $name,
                'id' => $name,
                'value' => $value
            ),
            $options
        );
        return $this->htmlTag($env, $context, 'input', $options);
    }

    public function textFieldTag(Environment $env, $context, $name, $default = null, $options = array())
    {
        $value = isset($context[$name]) ? $context[$name] : $default;
        return $this->inputTag($env, $context, 'text', $name, $value, $options);
    }

    public function textAreaTag(Environment $env, $context, $name, $default = null, $options = array())
    {
        $content = isset($context[$name]) ? $context[$name] : $default;
        $options = array_merge(
            array(
                'name' => $name,
                'id' => $name,
                'cols' => 60,
                'rows' => 5
            ),
            $options
        );
        return $this->contentTag($env, $context, 'textarea', $content, $options);
    }


    public function hiddenFieldTag(Environment $env, $context, $name, $default = null, $options = array())
    {
        $value = isset($context[$name]) ? $context[$name] : $default;
        return $this->inputTag($env, $context, 'hidden', $name, $value, $options);
    }

    public function passwordFieldTag(Environment $env, $context, $name = 'password', $default = null, $options = array())
    {
        $value = isset($context[$name]) ? $context[$name] : $default;
        return $this->inputTag($env, $context, 'password', $name, $value, $options);
    }

    public function radioButtonTag(Environment $env, $context, $name, $value, $default = false, $options = array())
    {
        if ((isset($context[$name]) and $context[$name] === $value) or (!isset($context[$name]) and $default))
        {
            $options = array_merge(array('checked' => 'checked'), $options);
        }
        $options = array_merge(array('id' => $name.'_'.$value), $options);
        return $this->inputTag($env, $context, 'radio', $name, $value, $options);
    }

    public function checkBoxTag(Environment $env, $context, $name, $value = '1', $default = false, $options = array())
    {
        if ((isset($context[$name]) and $context[$name] === $value) or (!isset($context['submit']) and $default))
        {
            $options = array_merge(array('checked' => 'checked'), $options);
        }
        return $this->inputTag($env, $context, 'checkbox', $name, $value, $options);
    }

    public function labelTag(Environment $env, $context, $name, $text = null, $options = array())
    {
        if (is_null($text)) {
            $text = ucwords(str_replace('_', ' ', $name)).': ';
        }
        $options = array_merge(
            array('for' => $name, 'id' => "label_for_$name"),
            $options
        );
        return $this->contentTag($env, $context, 'label', $text, $options);
    }

    public function labeledTextFieldTag(Environment $env, $context, $name, $default = null, $options = array())
    {
        return $this->labelTag($env, $context, $name).$this->textFieldTag($env, $context, $name, $default, $options);
    }

    public function selectTag(Environment $env, $context, $name, $options, $default = null, $html_options = array())
    {
        $opts = '';
        foreach ($options as $key => $label) {
            $arr = array('value' => $key);
            if ((isset($context[$name]) and $context[$name] === $key) or (!isset($context[$name]) and $default === $key))
            {
                $arr = array_merge(array('selected' => 'selected'),$arr);
            }
            $opts .= $this->contentTag($env, $context, 'option', $label, $arr);
        }
        $html_options = array_merge(
            array('name' => $name, 'id' => $name),
            $html_options
        );
        return "<select".$this->tagOptions($env, $html_options).">$opts</select>";
    }

    public function submitTag(Environment $env, $context, $value = 'Submit', $options = array())
    {
        if (isset($options['name'])) {
            $name = $options['name'];
        } else {
            $name = '';
        }
        return $this->inputTag($env, $context, 'submit', $name, $value, $options);
    }

    public function resetTag(Environment $env, $context, $value = 'Reset', $options = array())
    {
        if (isset($options['name'])) {
            $name = $options['name'];
        } else {
            $name = '';
        }
        return $this->inputTag($env, $context, 'reset', $name, $value, $options);
    }
}
