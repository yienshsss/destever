# GitHub SSH setup

Use separate SSH identities for work and personal GitHub accounts so repositories never borrow the wrong account.

## Windows keys created on this machine

- Work alias: `github-work`
- Personal alias: `github-personal`

Public keys:

```text
ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIBiit7CLHnNWN8/0aIPhBFEI7Q4AS4oQT5u3xbcl/B1I YGAFS-work
ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIClmfQlLzSBY4XkPJD/j+dyebq208kb7efLanoF8uK/Q yienshsss-personal
```

SSH config:

```sshconfig
Host github-work
  HostName github.com
  User git
  IdentityFile ~/.ssh/id_ed25519_work
  IdentitiesOnly yes

Host github-personal
  HostName github.com
  User git
  IdentityFile ~/.ssh/id_ed25519_personal
  IdentitiesOnly yes
```

## GitHub registration

1. Sign in to the work GitHub account.
2. Open Settings > SSH and GPG keys.
3. Add the `YGAFS-work` public key.
4. Sign in to the personal GitHub account.
5. Add the `yienshsss-personal` public key.

## Test commands on Windows

```powershell
ssh -T git@github-work
ssh -T git@github-personal
git -C Z:\docker\destever-source remote -v
git -C Z:\docker\destever-source push -u origin main
```

## Mac setup

Run these commands on the Mac:

```bash
mkdir -p ~/.ssh
chmod 700 ~/.ssh
ssh-keygen -t ed25519 -C "YGAFS-work" -f ~/.ssh/id_ed25519_work
ssh-keygen -t ed25519 -C "yienshsss-personal" -f ~/.ssh/id_ed25519_personal
cat > ~/.ssh/config <<'EOF'
Host github-work
  HostName github.com
  User git
  IdentityFile ~/.ssh/id_ed25519_work
  IdentitiesOnly yes
  AddKeysToAgent yes
  UseKeychain yes

Host github-personal
  HostName github.com
  User git
  IdentityFile ~/.ssh/id_ed25519_personal
  IdentitiesOnly yes
  AddKeysToAgent yes
  UseKeychain yes
EOF
chmod 600 ~/.ssh/config
ssh-add --apple-use-keychain ~/.ssh/id_ed25519_work
ssh-add --apple-use-keychain ~/.ssh/id_ed25519_personal
```

Then print the public keys and register them in the matching GitHub accounts:

```bash
cat ~/.ssh/id_ed25519_work.pub
cat ~/.ssh/id_ed25519_personal.pub
```

## Repository remote patterns

Use work repositories like:

```bash
git remote set-url origin git@github-work:YGAFS/repo-name.git
```

Use personal repositories like:

```bash
git remote set-url origin git@github-personal:yienshsss/destever.git
```
