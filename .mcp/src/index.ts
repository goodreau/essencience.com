#!/usr/bin/env node
import { Server } from "@modelcontextprotocol/sdk/server/index.js";
import { StdioServerTransport } from "@modelcontextprotocol/sdk/server/stdio.js";
import {
  CallToolRequestSchema,
  ListToolsRequestSchema,
} from "@modelcontextprotocol/sdk/types.js";
import simpleGit from "simple-git";
import { execSync } from "child_process";
import { existsSync } from "fs";
import path from "path";

const PROJECT_ROOT = path.resolve(process.cwd(), "..");
const git = simpleGit(PROJECT_ROOT);

const server = new Server(
  {
    name: "essencience-laravel-mcp",
    version: "1.0.0",
  },
  {
    capabilities: {
      tools: {},
    },
  }
);

// Helper to execute Laravel artisan commands
function runArtisan(command: string): string {
  try {
    const artisanPath = path.join(PROJECT_ROOT, "artisan");
    if (!existsSync(artisanPath)) {
      return "Error: artisan file not found. Laravel may not be installed.";
    }
    const output = execSync(`php ${artisanPath} ${command}`, {
      cwd: PROJECT_ROOT,
      encoding: "utf-8",
      maxBuffer: 10 * 1024 * 1024,
    });
    return output;
  } catch (error: any) {
    return `Error: ${error.message}\n${error.stdout || ""}`;
  }
}

// List available tools
server.setRequestHandler(ListToolsRequestSchema, async () => {
  return {
    tools: [
      {
        name: "git_status",
        description: "Get the current git status of the repository",
        inputSchema: {
          type: "object",
          properties: {},
        },
      },
      {
        name: "git_log",
        description: "Get git commit history",
        inputSchema: {
          type: "object",
          properties: {
            limit: {
              type: "number",
              description: "Number of commits to show (default: 10)",
            },
          },
        },
      },
      {
        name: "git_diff",
        description: "Show git diff for staged or unstaged changes",
        inputSchema: {
          type: "object",
          properties: {
            staged: {
              type: "boolean",
              description: "Show staged changes (default: false shows unstaged)",
            },
          },
        },
      },
      {
        name: "git_commit",
        description: "Commit staged changes with a message",
        inputSchema: {
          type: "object",
          properties: {
            message: {
              type: "string",
              description: "Commit message",
            },
          },
          required: ["message"],
        },
      },
      {
        name: "git_add",
        description: "Stage files for commit",
        inputSchema: {
          type: "object",
          properties: {
            files: {
              type: "string",
              description: "Files to stage (use '.' for all)",
            },
          },
          required: ["files"],
        },
      },
      {
        name: "artisan",
        description: "Run Laravel artisan commands",
        inputSchema: {
          type: "object",
          properties: {
            command: {
              type: "string",
              description: "Artisan command to run (e.g., 'migrate', 'make:model User')",
            },
          },
          required: ["command"],
        },
      },
      {
        name: "artisan_list",
        description: "List all available artisan commands",
        inputSchema: {
          type: "object",
          properties: {},
        },
      },
      {
        name: "composer",
        description: "Run composer commands",
        inputSchema: {
          type: "object",
          properties: {
            command: {
              type: "string",
              description: "Composer command to run (e.g., 'install', 'require livewire/livewire')",
            },
          },
          required: ["command"],
        },
      },
    ],
  };
});

// Handle tool calls
server.setRequestHandler(CallToolRequestSchema, async (request) => {
  const { name, arguments: args } = request.params;

  try {
    switch (name) {
      case "git_status": {
        const status = await git.status();
        return {
          content: [
            {
              type: "text",
              text: JSON.stringify(status, null, 2),
            },
          ],
        };
      }

      case "git_log": {
        const limit = (args?.limit as number) || 10;
        const log = await git.log({ maxCount: limit });
        return {
          content: [
            {
              type: "text",
              text: JSON.stringify(log, null, 2),
            },
          ],
        };
      }

      case "git_diff": {
        const staged = (args?.staged as boolean) || false;
        const diff = staged
          ? await git.diff(["--cached"])
          : await git.diff();
        return {
          content: [
            {
              type: "text",
              text: diff || "No changes",
            },
          ],
        };
      }

      case "git_commit": {
        const message = args?.message as string;
        const result = await git.commit(message);
        return {
          content: [
            {
              type: "text",
              text: `Committed: ${result.commit}\n${result.summary.changes} files changed, ${result.summary.insertions} insertions(+), ${result.summary.deletions} deletions(-)`,
            },
          ],
        };
      }

      case "git_add": {
        const files = args?.files as string;
        await git.add(files);
        return {
          content: [
            {
              type: "text",
              text: `Staged: ${files}`,
            },
          ],
        };
      }

      case "artisan": {
        const command = args?.command as string;
        const output = runArtisan(command);
        return {
          content: [
            {
              type: "text",
              text: output,
            },
          ],
        };
      }

      case "artisan_list": {
        const output = runArtisan("list");
        return {
          content: [
            {
              type: "text",
              text: output,
            },
          ],
        };
      }

      case "composer": {
        const command = args?.command as string;
        try {
          const output = execSync(`composer ${command}`, {
            cwd: PROJECT_ROOT,
            encoding: "utf-8",
            maxBuffer: 10 * 1024 * 1024,
          });
          return {
            content: [
              {
                type: "text",
                text: output,
              },
            ],
          };
        } catch (error: any) {
          return {
            content: [
              {
                type: "text",
                text: `Error: ${error.message}\n${error.stdout || ""}`,
              },
            ],
          };
        }
      }

      default:
        throw new Error(`Unknown tool: ${name}`);
    }
  } catch (error: any) {
    return {
      content: [
        {
          type: "text",
          text: `Error: ${error.message}`,
        },
      ],
      isError: true,
    };
  }
});

async function main() {
  const transport = new StdioServerTransport();
  await server.connect(transport);
  console.error("Essencience Laravel MCP server running on stdio");
}

main().catch((error) => {
  console.error("Server error:", error);
  process.exit(1);
});
