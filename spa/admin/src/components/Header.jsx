import React from 'react'
import { helpTabs } from '../data';
import { Link } from 'react-router-dom';

const Header = () => {

    return (
        <div className='otp-shield_header'>
            <div id="otp-shield_tabs">
                <ul>
                {helpTabs.map((route, index) => (
                    <li class={ window.location.hash == '#' + route.path ? "active" : "" }><Link to={route.path}>{route.label}</Link></li>
                ))}
                </ul>
            </div>
        </div>
    );
}

export default Header;