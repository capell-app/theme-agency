<?php

declare(strict_types=1);

namespace Capell\AgentBridge\Servers;

use Capell\AgentBridge\Tools\Site\ConfirmSiteCapabilityTool;
use Capell\AgentBridge\Tools\Site\InspectSiteStateTool;
use Capell\AgentBridge\Tools\Site\ListSiteCapabilitiesTool;
use Capell\AgentBridge\Tools\Site\QuerySiteAuditEntriesTool;
use Capell\AgentBridge\Tools\Site\RunSiteCapabilityTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Capell Site Agent Bridge')]
#[Version('0.1.0')]
#[Instructions('Authenticated Agent Bridge server for inspecting and safely operating an installed Capell CMS site.')]
final class CapellSiteServer extends Server
{
    protected array $tools = [
        ListSiteCapabilitiesTool::class,
        InspectSiteStateTool::class,
        QuerySiteAuditEntriesTool::class,
        RunSiteCapabilityTool::class,
        ConfirmSiteCapabilityTool::class,
    ];
}
