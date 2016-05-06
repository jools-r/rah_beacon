<?php

/*
 * rah_beacon - Create alias tags using form partials in Textpattern CMS
 * https://github.com/gocom/rah_beacon
 *
 * Copyright (C) 2015 Jukka Svahn
 *
 * This file is part of rah_beacon.
 *
 * rah_beacon is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation, version 2.
 *
 * rah_beacon is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with rah_beacon. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * The plugin class.
 */

class Rah_Beacon
{
    /**
     * Constructor.
     */

    public function __construct()
    {
        Txp::get('\Textpattern\Tag\Registry')->register(array($this, 'atts'), 'rah_beacon_atts');
        register_callback(array($this, 'light'), 'pretext');
    }

    /**
     * Registers forms as tags.
     */

    public function light()
    {
        global $trace;
        
        $forms = safe_column(
            'name',
            'txp_form',
            '1 = 1'
        );

        $beacon = new Rah_Beacon_Handler();

        foreach ($forms as $name) {
            // allowed form names: lowercase, digits and underscore
            // forms must begin with a letter
            if (!preg_match('/^[a-z][a-z0-9_]*$/', $name)) {
                $trace->log('[rah_beacon: '.$name.' skipped]');
                continue;
            }
          
            // disallow form names that collide with pre-existing registered tags
            if (Txp::get('\Textpattern\Tag\Registry')->isRegistered($name)) {
                 $trace->log('[rah_beacon: '.$name.' skipped – tag already exists]');
                continue;
            }

            Txp::get('\Textpattern\Tag\Registry')->register(array($beacon, $name), $name);
        }
    }

    /**
     * A tag for assigning attribute defaults for tags.
     *
     * This tag should be called within the Form partial if
     * it requires defaults for it's variables.
     *
     * <code>
     * <txp:rah_beacon_atts color="blue" size="small" />
     * </code>
     *
     * The above would create a variable named "color" and "size"
     * with values "blue" and "small" if one of them isn't
     * specified as attributes in the tag calling the form.
     *
     * @param  array $atts Attributes
     * @return null
     */

    public function atts($atts)
    {
        global $variable;
    
        foreach (lAtts($atts, $variable, false) as $name => $value) {
            $variable[$name] = $value;
        }
    }
}

new Rah_Beacon();
