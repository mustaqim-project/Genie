# BRIEFING — 2026-11-12T15:45:00Z

## Mission
Deliver Milestone 1 (Foundation, Multi-Container Normalization & Workflow Separation) for the GENIE project via disciplined Iteration Loop (Explorer -> Worker -> Reviewer -> Challenger -> Auditor).

## 🔒 My Identity
- Archetype: teamwork_orchestrator
- Roles: orchestrator, user_liaison, human_reporter, successor
- Working directory: c:\laragon\www\GENIE
- State folder: c:\laragon\www\GENIE\.agents\teamwork\sub_orch_milestone_1
- Original parent: Project Orchestrator
- Original parent conversation ID: b0ddf190-c06b-41f0-99dc-4f1c82097b30

## 🔒 My Workflow
- **Pattern**: Project Pattern (2B Iteration Loop)
- **Scope document**: c:\laragon\www\GENIE\.agents\teamwork\sub_orch_milestone_1\SCOPE.md
1. **Decompose**: Scope is sized for single milestone iteration loop encompassing container normalization, import/export state machines, and cross-document validation engine integration.
2. **Dispatch & Execute**: Direct (iteration loop):
   - Step a: Dispatch 3 Explorers (analyze current schema, container models, state machines, cross-document engine).
   - Step b: Dispatch Worker with integrity warning to implement migration, models, state machines, validation engine, and unit tests.
   - Step c: Dispatch 2 Reviewers independently to verify code quality, backward compatibility, and test passes.
   - Step d: Dispatch 2 Challengers to empirically verify container modulo-11 and state transitions.
   - Step e: Dispatch Forensic Auditor (teamwork_auditor) for binary veto integrity verification.
   - Step f: Gate check in GATE_STATUS.md. All pass -> mark milestone complete and notify parent.
3. **On failure** (in this order):
   - Retry: nudge stuck agent or re-send task
   - Replace: spawn fresh agent with partial progress
   - Skip: proceed without (only if non-critical)
   - Redistribute: split stuck agent's remaining work
   - Redesign: re-partition decomposition
   - Escalate: report to parent (sub-orchestrators only, last resort)
4. **Succession**: At 16 spawns, write handoff.md, cancel background tasks, spawn successor into `_gen<N>`.
- **Work items**:
  1. Multi-Container Normalization & ISO 6346 Validation [in-progress]
  2. Import & Export Workflow State Machines [in-progress]
  3. Cross-Document Validation Engine Integration (Rules V01–V07) [in-progress]
- **Current phase**: 2B (Iteration 1: Exploration)
- **Current focus**: Parallel exploration across containers, state machines, and cross-document validation

## 🔒 Key Constraints
- Never write, modify, or create source code files directly.
- Never run build/test commands yourself — require workers to do so.
- Never explore the problem at the code level — dispatch Explorers.
- Audit is a binary veto: if auditor reports integrity violation, iteration fails unconditionally.
- Never reuse a subagent after it has delivered its handoff — always spawn fresh.
- Always include path to ORIGINAL_REQUEST.md in every subagent dispatch.

## Current Parent
- Conversation ID: b0ddf190-c06b-41f0-99dc-4f1c82097b30
- Updated: 2026-11-12T15:45:00Z

## Key Decisions Made
- Decomposed Milestone 1 into a unified iteration loop covering containers, state machines, and cross-document validation to guarantee cohesive integration across Shipment model and workflow services.
- Dispatched 3 specialized Explorers in parallel to establish precise technical designs.

## Team Roster
| Agent | Type | Work Item | Status | Conv ID |
|-------|------|-----------|--------|---------|
| explorer_containers | teamwork_explorer | Multi-Container Normalization & ISO 6346 Validation | in-progress | dc112edc-967c-4bde-9ddc-98f77d5a0001 |
| explorer_statemachine | teamwork_explorer | Import & Export Workflow State Machines | in-progress | 2ef89a23-009a-4c84-8774-59cab6ba7dee |
| explorer_crossval | teamwork_explorer | Cross-Document Validation Engine Integration | in-progress | dba204b5-d2fe-4f7f-a5ba-9b35cd82cb41 |

## Succession Status
- Succession required: no
- Spawn count: 3 / 16
- Pending subagents: dc112edc-967c-4bde-9ddc-98f77d5a0001, 2ef89a23-009a-4c84-8774-59cab6ba7dee, dba204b5-d2fe-4f7f-a5ba-9b35cd82cb41
- Predecessor: none
- Successor spawned: not yet spawned
- Successor generation: none
- Successor state folder: none

## Active Timers
- Heartbeat cron: c9a703fa-acca-47ce-93a9-b7983725bd6a/task-22
- Safety timer: none
- On succession: kill all timers before spawning successor
- On context truncation: handle timers as step 4 of the State Recovery section of your prompt says

## Artifact Index
- c:\laragon\www\GENIE\.agents\teamwork\sub_orch_milestone_1\DISPATCH.md — Incoming assignment record
- c:\laragon\www\GENIE\.agents\teamwork\sub_orch_milestone_1\BRIEFING.md — Working memory and identity
- c:\laragon\www\GENIE\.agents\teamwork\sub_orch_milestone_1\progress.md — Liveness heartbeat and milestone progress
- c:\laragon\www\GENIE\.agents\teamwork\sub_orch_milestone_1\SCOPE.md — Milestone 1 scope definition and interface contracts
- c:\laragon\www\GENIE\.agents\teamwork\sub_orch_milestone_1\GATE_STATUS.md — Iteration gate check ledger
