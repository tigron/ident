# ident

## Description

This package currently implements a client for the RFC1413 (IDENT) protocol. I
don't currently have plans to add a server, but making one would be trivial.

## Installation

Install this package via composer:

    composer require tigron/ident

## Usage

Very simple:

    $ident = new Tigron\Ident\IdentClient();
    echo $ident->getUser();
    echo $ident->getOsType();

Optionally, you can

The constructor accepts some options as well:

    $ident = new Tigron\Ident\IdentClient($remote_address, $remote_port, $local_port, $ident_port, $timeout);

Some setters are provided, for your convenience:

    $ident->setRemoteAddress($remote_address);
    $ident->setRemotePort($remote_port);
    $ident->setLocalPort($local_port);
    $ident->setIdentPort($ident_port);
    $ident->setTimeout($timeout);
